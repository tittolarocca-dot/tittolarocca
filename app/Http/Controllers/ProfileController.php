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

        $trialSub = $user ? $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->where('status', 'trialing')
            ->where('current_period_end', '>', now())
            ->first() : null;

        $hasTrialed = $user ? $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->exists() : false;

        $allPublicMedia = $profile->publicMedia()->get(['id', 'type', 'visibility', 'is_main']);
        $mainMedia  = $allPublicMedia->firstWhere('is_main', true);
        $publicMedia = $mainMedia
            ? $allPublicMedia->where('id', '!=', $mainMedia->id)->values()
            : $allPublicMedia;

        $privateMediaCount = $profile->privateMedia()->count();
        $privateMedia = ($isOwner || $subscribed || $trialSub)
            ? $profile->privateMedia()->get(['id', 'type', 'visibility'])
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
                'gender'                 => $profile->gender,
                'sexuality'              => $profile->sexuality,
                'body_type'              => $profile->body_type,
                'height_cm'              => $profile->height_cm,
                'weight_kg'              => $profile->weight_kg,
                'smoker'                 => $profile->smoker,
                'city'                   => $profile->city?->name,
                'city_slug'              => $profile->city?->slug,
                'category'               => $profile->category?->name,
                'category_slug'          => $profile->category?->slug,
                'tags'                   => $profile->tags->pluck('name'),
                'subscription_price_chf' => $profile->subscription_price_chf,
                'total_subscribers'      => $profile->total_subscribers,
                'total_views'            => $profile->total_views,
                'whatsapp_number'        => $profile->whatsapp_number,
                'phone_number'           => $profile->phone_number,
                'telegram_username'      => $profile->telegram_username,
                'address'                => $profile->address,
                'created_at'             => $profile->created_at->format('d.m.Y'),
                'verification_status'    => $profile->verification_status,
            ],
            'mainMedia'           => $mainMedia ? ['id' => $mainMedia->id, 'type' => $mainMedia->type, 'url' => route('media.stream', $mainMedia->id)] : null,
            'publicMedia'         => $publicMedia,
            'privateMedia'        => $privateMedia,
            'privateMediaCount'   => $privateMediaCount,
            'reviews'            => $reviews,
            'isOwner'             => $isOwner,
            'isSubscribed'        => $subscribed,
            'isTrialing'          => (bool) $trialSub,
            'trialEndsAt'         => $trialSub?->current_period_end?->format('d.m.Y'),
            'trialDaysLeft'       => $trialSub ? max(0, (int) now()->diffInDays($trialSub->current_period_end, false)) : 0,
            'hasTrialed'          => $hasTrialed,
            'hasSubscriptionOffer'=> $profile->subscription_price_chf > 0,
            'hasReviewed'         => $user ? \App\Models\Review::where('reviewer_user_id', $user->id)->where('profile_id', $profile->id)->exists() : false,
            'subscribed'          => $request->query('subscribed') === '1',
        ]);
    }
}
