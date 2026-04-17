<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
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

        $profile->loadMissing(['city', 'category', 'tags', 'approvedReviews.user']);
        $profile->increment('total_views');

        $publicMedia = $profile->publicMedia()
            ->get()
            ->map(fn($m) => [
                'id'   => $m->id,
                'type' => $m->type,
                'url'  => route('media.stream', $m->id),
            ]);

        $privateMedia = ($isOwner || $subscribed)
            ? $profile->privateMedia()
                ->get()
                ->map(fn($m) => [
                    'id'   => $m->id,
                    'type' => $m->type,
                    'url'  => route('media.stream', $m->id),
                ])
            : collect([]);

        $reviews = $profile->approvedReviews()
            ->with('reviewer:id,name')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'stars'      => $r->stars,
                'comment'    => $r->comment,
                'reply'      => $r->reply_status === 'approved' ? $r->inserent_reply : null,
                'author'     => $r->reviewer->name,
                'created_at' => $r->created_at->format('d.m.Y'),
            ]);

        return Inertia::render('Profile/Show', [
            'profile' => [
                'id'                     => $profile->id,
                'slug'                   => $profile->slug,
                'display_name'           => $profile->display_name,
                'description'            => $profile->description,
                'age'                    => $profile->age,
                'city'                   => $profile->city?->name,
                'category'               => $profile->category?->name,
                'tags'                   => $profile->tags->pluck('name'),
                'subscription_price_chf' => $profile->subscription_price_chf,
                'total_subscribers'      => $profile->total_subscribers,
                'total_views'            => $profile->total_views,
                // Only reveal to owner/subscriber
                'whatsapp_number'        => ($isOwner || $subscribed) ? $profile->whatsapp_number : null,
            ],
            'publicMedia'        => $publicMedia,
            'privateMedia'       => $privateMedia,
            'reviews'            => $reviews,
            'isOwner'             => $isOwner,
            'isSubscribed'        => $subscribed,
            'hasSubscriptionOffer'=> $profile->subscription_price_chf > 0,
            'hasReviewed'         => $user ? \App\Models\Review::where('reviewer_user_id', $user->id)->where('profile_id', $profile->id)->exists() : false,
            'subscribed'          => $request->query('subscribed') === '1',
        ]);
    }
}
