<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Review;
use App\Notifications\NewReviewNotification;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Profile $profile)
    {
        $user = $request->user();

        if (!$user->isSubscribedTo($profile)) {
            return back()->with('error', 'Nur Abonnenten können eine Bewertung abgeben.');
        }

        // One review per profile
        if (Review::where('reviewer_user_id', $user->id)->where('profile_id', $profile->id)->exists()) {
            return back()->with('error', 'Du hast dieses Profil bereits bewertet.');
        }

        $request->validate([
            'stars'   => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $sub = $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->first();

        Review::create([
            'reviewer_user_id'       => $user->id,
            'profile_id'             => $profile->id,
            'platform_subscription_id' => $sub?->id,
            'stars'                  => $request->stars,
            'comment'                => $request->comment,
            'status'                 => 'pending',
            'reviewed_at'            => now(),
        ]);

        // Notify inserent
        try {
            $profile->user->notify(new NewReviewNotification($profile, $request->stars));
        } catch (\Exception $e) {}

        return back()->with('success', 'Bewertung eingereicht. Sie wird nach Prüfung freigeschaltet.');
    }

    public function reply(Request $request, Review $review)
    {
        $user = $request->user();

        if ($review->profile->user_id !== $user->id) {
            abort(403);
        }

        $request->validate(['reply' => ['required', 'string', 'max:500']]);

        $review->update([
            'inserent_reply' => $request->reply,
            'reply_status'   => 'pending',
        ]);

        return back()->with('success', 'Antwort eingereicht. Sie wird nach Prüfung freigeschaltet.');
    }
}
