<?php

namespace App\Http\Controllers;

use App\Models\ProfilePost;
use App\Models\User;
use App\Support\RelativeTime;
use App\Support\SeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Globaler Community-Feed: führt die veröffentlichten, öffentlichen Beiträge
 * aller aktiven Profile zusammen (neueste zuerst). Nutzt die bestehende
 * ProfilePost-/Media-/Follow-Infrastruktur – kein paralleles System.
 */
class FeedController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request)
    {
        $user = $request->user();

        $paginator = $this->feedQuery()->paginate(self::PER_PAGE)->withQueryString();

        $data = [
            'posts'       => $this->mapPosts($paginator->getCollection(), $user),
            'nextPageUrl' => $paginator->nextPageUrl(),
        ];

        // Infinite Scroll: Folgeseiten werden per fetch als JSON nachgeladen.
        if ($request->boolean('json')) {
            return response()->json($data);
        }

        app(SeoData::class)
            ->forPage(__('feed.seo_title'), __('feed.seo_desc'))
            ->setCanonical(route('feed'));

        return Inertia::render('Feed/Index', $data + [
            'isAuthed' => (bool) $user,
        ]);
    }

    /**
     * Nur VERÖFFENTLICHTE, ÖFFENTLICHE Beiträge von AKTIVEN Profilen.
     * Entwürfe (published_at null/zukünftig), followers-/private-Beiträge und
     * inaktive/abgelaufene Profile sind ausgeschlossen.
     */
    private function feedQuery()
    {
        return ProfilePost::query()
            ->where('visibility', 'public')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('profile', fn ($q) => $q
                ->where('status', 'active')
                ->where('listing_expires_at', '>', now()))
            ->with([
                'profile:id,user_id,display_name,slug,city_id,verification_status,identity_verification_status',
                'profile.city:id,name',
                'profile.publicMedia' => fn ($q) => $q->where('type', 'image')->orderBy('sort_order'),
                'media',
            ])
            ->orderByDesc('published_at');
    }

    private function mapPosts($posts, ?User $user): array
    {
        // Welche Profile folgt der/die Betrachter:in? (bestehende Follow-Relation)
        $followed = $user
            ? DB::table('profile_follows')->where('follower_user_id', $user->id)->pluck('profile_id')->flip()
            : collect();

        return $posts->map(function ($p) use ($followed) {
            $profile = $p->profile;
            $avatar  = $profile?->publicMedia->first();

            return [
                'id'    => $p->id,
                'text'  => $p->text,
                'time'  => RelativeTime::short($p->published_at ?? $p->created_at),
                'media' => $p->media->map(fn ($m) => [
                    'id'   => $m->id,
                    'type' => $m->type,
                    'src'  => $m->type === 'image' ? ($m->src['card'] ?? $m->url) : $m->url,
                    'full' => $m->type === 'image' ? ($m->src['full'] ?? $m->url) : $m->url,
                ])->values(),
                'author' => [
                    'name'         => $profile?->display_name,
                    'slug'         => $profile?->slug,
                    'city'         => $profile?->city?->name,
                    'verified'     => $profile && ($profile->identity_verification_status === 'approved'
                                        || $profile->verification_status === 'approved'),
                    'avatar_url'   => $avatar ? ($avatar->src['thumbnail'] ?? $avatar->url) : null,
                    'is_following' => $profile ? $followed->has($profile->id) : false,
                ],
            ];
        })->values()->all();
    }
}
