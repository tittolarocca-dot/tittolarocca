<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfilePost;
use App\Models\ProfileVisit;
use App\Models\User;
use App\Support\RelativeTime;
use App\Support\SeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request, Profile $profile)
    {
        if (!$profile->isActive()) {
            abort(404);
        }

        $user       = $request->user();
        $isOwner    = $user && $profile->user_id === $user->id;
        $subscribed = $user && $user->isSubscribedTo($profile);

        $profile->loadMissing(['city', 'category', 'categories', 'tags', 'approvedReviews.reviewer', 'user']);
        $profile->increment('total_views');

        // SEO: Titel = Anzeigename, Description aus der Profilbeschreibung (Fallback generisch)
        $rawDesc = trim(strip_tags((string) $profile->description));
        app(SeoData::class)->forPage(
            $profile->display_name,
            $rawDesc !== ''
                ? Str::limit($rawDesc, 155)
                : $profile->display_name . ' – Begleitung & Erotik'
                    . ($profile->city ? ' in ' . $profile->city->name : '')
                    . '. Profil mit Fotos auf booklola.ch.'
        );

        if ($user && !$isOwner) {
            ProfileVisit::updateOrCreate(
                ['profile_id' => $profile->id, 'visitor_user_id' => $user->id],
                ['profile_owner_user_id' => $profile->user_id, 'last_visited_at' => now()]
            );
        }

        $trialSub = $user ? $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->where('status', 'trialing')
            ->where('current_period_end', '>', now())
            ->first() : null;

        $hasTrialed = $user ? $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->exists() : false;

        // Freigeschaltete Medien liefern optimierte Varianten (Bilder) bzw. den
        // Original-Stream (Videos); gesperrte NUR die serverseitig erzeugte
        // Blur-Vorschau (niemals ein scharfes Bild).
        $cols = ['id', 'type', 'visibility', 'variants', 'width', 'height', 'updated_at'];

        $streamItem = fn ($m) => [
            'id'         => $m->id,
            'type'       => $m->type,
            'visibility' => $m->visibility,
            'width'      => $m->width,
            'height'     => $m->height,
            'url'        => route('media.stream', $m->id), // Video + Fallback
            'src'        => $m->src,                        // Bild-Varianten (null bei Video)
        ];
        $lockedItem = fn ($m) => [
            'id'          => $m->id,
            'type'        => $m->type,
            'visibility'  => $m->visibility,
            'preview_url' => route('media.preview', $m->id),
        ];

        $publicMedia = $profile->publicMedia()->get($cols)
            ->map($streamItem)->values();

        // SEO: OG-Bild (Hauptfoto) + strukturierte Daten (Breadcrumb + Person)
        $firstImage = $publicMedia->firstWhere('type', 'image');
        $ogImage    = ($firstImage && ! empty($firstImage['src']))
            ? ($firstImage['src']['full'] ?? $firstImage['src']['card'] ?? null)
            : null;

        $crumbs = [['Startseite', route('home')]];
        if ($profile->city) {
            $crumbs[] = [$profile->city->name, route('city', $profile->city->slug)];
        }
        $crumbs[] = [$profile->display_name, url()->current()];

        app(SeoData::class)
            ->setCanonical(route('profile.show', $profile->slug))
            ->setOg('profile', $ogImage)
            ->addBreadcrumb($crumbs)
            ->addJsonLd(array_filter([
                '@context' => 'https://schema.org',
                '@type'    => 'Person',
                'name'     => $profile->display_name,
                'url'      => url()->current(),
                'image'    => $ogImage,
                'address'  => $profile->city ? [
                    '@type'           => 'PostalAddress',
                    'addressLocality' => $profile->city->name,
                    'addressCountry'  => 'CH',
                ] : null,
            ]));

        // Launch-Modus: Inserentin kann private Galerie kostenlos für registrierte
        // Mitglieder freigeben. Niemals für Gäste – nur eingeloggte Mitglieder.
        // Zusätzlich kann ein Mitglied individuell Zugang anfragen; die Inserentin
        // gibt pro Person frei (private_gallery_requests).
        $launchMode        = (bool) config('features.launch_mode');
        $launchGalleryFree = (bool) $profile->launch_gallery_free;

        $galleryRequest = ($launchMode && $user && ! $isOwner)
            ? \App\Models\PrivateGalleryRequest::where('profile_id', $profile->id)
                ->where('user_id', $user->id)->first()
            : null;
        $galleryApproved = $galleryRequest?->status === 'approved';

        $launchUnlocked = $launchMode && $user !== null && ($launchGalleryFree || $galleryApproved);

        $unlocked      = $isOwner || $subscribed || (bool) $trialSub || $launchUnlocked;
        $privateItems  = $profile->privateMedia()->get($cols);
        $privateMediaCount = $privateItems->count();
        $privateMedia = $unlocked
            ? $privateItems->map($streamItem)->values()
            : $privateItems->map($lockedItem)->values();

        // ── Geoblocking: Besucher aus gesperrten Ländern sehen keine Fotos/Kontaktdaten ──
        $visitorCountry = app(\App\Support\VisitorCountry::class)->for($request);
        $geoBlocked     = ! $isOwner && $profile->isBlockedInCountry($visitorCountry);
        if ($geoBlocked) {
            $publicMedia       = collect();
            $privateMedia      = collect();
            $privateMediaCount = 0;
        }

        $reviews = $profile->approvedReviews()
            ->with('reviewer:id,name')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'stars'        => $r->stars,
                'comment'      => $r->comment,
                'reply'        => $r->reply_status === 'approved' ? $r->inserent_reply : null,
                'reply_status' => $r->reply_status, // für Owner-Hinweis (Text nur wenn approved)
                'author'       => $r->reviewer->name,
                'created_at'   => $r->created_at->format('d.m.Y'),
            ]);

        // Gesprochene Sprachen als geordnete Liste { code, level }
        $langOrder    = ['de', 'en', 'fr', 'es', 'it', 'hu', 'ro', 'pt', 'ru', 'other'];
        $profileLangs = $profile->languages ?? [];
        $languages    = [];
        foreach ($langOrder as $c) {
            $lvl = (int) ($profileLangs[$c] ?? 0);
            if ($lvl >= 1) {
                $languages[] = ['code' => $c, 'level' => $lvl];
            }
        }

        // Eigene Bewertung (auch pending/rejected) – damit der Nutzer den Status sieht
        $myReview = $user
            ? \App\Models\Review::where('reviewer_user_id', $user->id)
                ->where('profile_id', $profile->id)
                ->first(['id', 'stars', 'comment', 'status'])
            : null;

        // ── Bewertungs-Zusammenfassung (server-seitig, volle Menge) ──
        $ratingCount = $profile->approvedReviews()->count();
        $ratingAvg   = $ratingCount > 0
            ? round((float) $profile->approvedReviews()->avg('stars'), 1)
            : null;

        // ── Follow (getrennt von Favorit) ──
        $followersCount = $profile->followers()->count();
        $isFollowing    = $user ? $user->follows()->where('profile_id', $profile->id)->exists() : false;

        // ── Aktivitätsstatus (nur wenn freigegeben) ──
        $activity = null;
        if ($profile->show_activity_status) {
            $lastActive = $profile->user?->last_active_at;
            if ($lastActive) {
                $activity = [
                    'online'    => RelativeTime::isOnline($lastActive),
                    'last_seen' => RelativeTime::short($lastActive),
                ];
            }
        }

        // ── Feed: max. 3 neueste sichtbare Beiträge ──
        $feedPosts = $this->mapFeedPosts($this->visibleFeedQuery($profile, $user)->take(3)->get(), $user);

        return Inertia::render('Profile/Show', [
            'profile' => [
                'id'                     => $profile->id,
                'slug'                   => $profile->slug,
                'display_name'           => $profile->display_name,
                'description'            => $profile->description,
                'age'                    => $profile->age,
                'nationality'            => $profile->nationality,
                'height_cm'              => $profile->height_cm,
                'eye_color'              => $profile->eye_color,
                'smoking'                => $profile->smoking,
                'tattoo'                 => $profile->tattoo,
                'intimate_area'          => $profile->intimate_area,
                'body_type'              => $profile->body_type,
                'origin'                 => $profile->origin,
                'weight_kg'              => $profile->weight_kg,
                'cup_size'               => $profile->cup_size,
                'breast_type'            => $profile->breast_type,
                'city'                   => $profile->city?->name,
                'city_slug'              => $profile->city?->slug,
                'category'               => $profile->category?->name,
                'category_slug'          => $profile->category?->slug,
                'categories'             => $profile->categories->map(fn ($c) => [
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->values(),
                'tags'                   => $profile->tags->pluck('name'),
                'subscription_price_chf' => $profile->subscription_price_chf,
                'total_subscribers'      => $profile->total_subscribers,
                'total_views'            => $profile->total_views,
                'likes_count'            => $profile->likedBy()->count(),
                'followers_count'        => $followersCount,
                'whatsapp_number'        => $geoBlocked ? null : $profile->whatsapp_number,
                'telegram_username'      => $geoBlocked ? null : $profile->telegram_username,
                'address'                => $geoBlocked ? null : $profile->address,
                'website'                => $geoBlocked ? null : $profile->website,
                'created_at'             => $profile->created_at->format('d.m.Y'),
                'verification_status'          => $profile->verification_status,
                'identity_verification_status' => $profile->identity_verification_status,
            ],
            'languages'           => $languages,
            'publicMedia'         => $publicMedia,
            'privateMedia'        => $privateMedia,
            'privateMediaCount'   => $privateMediaCount,
            'geoBlocked'          => $geoBlocked,
            'reviews'            => $reviews,
            'isOwner'             => $isOwner,
            'isSubscribed'        => $subscribed,
            'isTrialing'          => (bool) $trialSub,
            'trialEndsAt'         => $trialSub?->current_period_end?->format('d.m.Y'),
            'trialDaysLeft'       => $trialSub ? max(0, (int) now()->diffInDays($trialSub->current_period_end, false)) : 0,
            'hasTrialed'          => $hasTrialed,
            'hasSubscriptionOffer'=> $profile->subscription_price_chf > 0,
            'hasReviewed'         => (bool) $myReview,
            'myReview'            => $myReview,
            'isFavorited'         => $user ? $user->favorites()->where('profile_id', $profile->id)->exists() : false,
            'isLiked'             => $user ? $user->likes()->where('profile_id', $profile->id)->exists() : false,
            'isFollowing'         => $isFollowing,
            'ratingAvg'           => $ratingAvg,
            'ratingCount'         => $ratingCount,
            'activity'            => $activity,
            'feedPosts'           => $feedPosts,
            'feedHasMore'         => $this->visibleFeedQuery($profile, $user)->count() > 3,
            'launchMode'          => $launchMode,
            'launchGalleryFree'   => $launchGalleryFree,
            'isLaunchUnlocked'    => $launchUnlocked,
            'galleryRequestStatus'=> $galleryRequest?->status, // null | pending | approved | declined
            'subscribed'          => $request->query('subscribed') === '1',
        ]);
    }

    /** Öffentliche „Alle Beiträge"-Seite eines Profils. */
    public function feed(Request $request, Profile $profile)
    {
        if (! $profile->isActive()) {
            abort(404);
        }

        $user = $request->user();
        $profile->loadMissing('user');

        $paginator = $this->visibleFeedQuery($profile, $user)->paginate(10)->withQueryString();

        app(SeoData::class)
            ->forPage($profile->display_name . ' – Feed')
            ->setCanonical(route('profile.feed', $profile->slug));

        return Inertia::render('Profile/Feed', [
            'profile'     => ['slug' => $profile->slug, 'display_name' => $profile->display_name],
            'posts'       => $this->mapFeedPosts($paginator->getCollection(), $user),
            'nextPageUrl' => $paginator->nextPageUrl(),
            'isFollowing' => $user ? $user->follows()->where('profile_id', $profile->id)->exists() : false,
        ]);
    }

    /** Query über die für $user sichtbaren, veröffentlichten Beiträge eines Profils. */
    private function visibleFeedQuery(Profile $profile, ?User $user)
    {
        $q = $profile->posts()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $isOwner = $user && $profile->user_id === $user->id;
        if (! $isOwner) {
            $allowed = ['public'];
            if ($user && $user->follows()->where('profile_id', $profile->id)->exists()) {
                $allowed[] = 'followers';
            }
            $q->whereIn('visibility', $allowed);
        }

        return $q->with('media')->withCount('likedBy')->orderByDesc('published_at');
    }

    /** Feed-Beiträge für die Ausgabe aufbereiten (inkl. Like-Status des Betrachters). */
    private function mapFeedPosts($posts, ?User $user): array
    {
        $ids      = $posts->pluck('id');
        $likedIds = ($user && $ids->isNotEmpty())
            ? DB::table('profile_post_likes')
                ->where('user_id', $user->id)
                ->whereIn('post_id', $ids)
                ->pluck('post_id')->flip()
            : collect();

        return $posts->map(fn ($p) => [
            'id'         => $p->id,
            'text'       => $p->text,
            'visibility' => $p->visibility,
            'post_type'  => $p->post_type,
            'time'       => RelativeTime::short($p->published_at ?? $p->created_at),
            'likes'      => $p->liked_by_count ?? 0,
            'liked'      => $likedIds->has($p->id),
            'media'      => $p->media->map(fn ($m) => [
                'id'   => $m->id,
                'type' => $m->type,
                'src'  => $m->type === 'image' ? ($m->src['card'] ?? $m->url) : $m->url,
                'full' => $m->type === 'image' ? ($m->src['full'] ?? $m->url) : $m->url,
            ])->values(),
        ])->values()->all();
    }
}
