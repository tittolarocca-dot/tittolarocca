<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileVisit;
use App\Support\SeoData;
use Illuminate\Http\Request;
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

        $profile->loadMissing(['city', 'category', 'categories', 'tags', 'approvedReviews.reviewer']);
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
                'followers_count'        => $profile->favoritedBy()->count(),
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
            'launchMode'          => $launchMode,
            'launchGalleryFree'   => $launchGalleryFree,
            'isLaunchUnlocked'    => $launchUnlocked,
            'galleryRequestStatus'=> $galleryRequest?->status, // null | pending | approved | declined
            'subscribed'          => $request->query('subscribed') === '1',
        ]);
    }
}
