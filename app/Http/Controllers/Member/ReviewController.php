<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Review;
use App\Notifications\NewReviewNotification;
use App\Rules\CleanReviewText;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Profile $profile)
    {
        $user = $request->user();

        // Nur registrierte Abonnenten (die Profile ansehen/abonnieren können) dürfen bewerten.
        if (!$user->isSubscribedTo($profile)) {
            return back()->with('error', 'Nur Abonnenten können eine Bewertung abgeben.');
        }

        // Höchstens eine Bewertung pro Benutzer und Inserat
        if (Review::where('reviewer_user_id', $user->id)->where('profile_id', $profile->id)->exists()) {
            return back()->with('error', 'Du hast dieses Profil bereits bewertet.');
        }

        $data = $request->validate([
            'stars'   => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000', new CleanReviewText()],
        ]);

        $sub = $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->first();

        $review = Review::create([
            'reviewer_user_id'         => $user->id,
            'profile_id'               => $profile->id,
            'platform_subscription_id' => $sub?->id,
            'stars'                    => $data['stars'],
            'comment'                  => $data['comment'] ?? null,
            'status'                   => 'pending',
            'reviewed_at'              => now(),
        ]);

        try {
            $profile->user->notify(new NewReviewNotification($profile, $review->stars));
        } catch (\Throwable $e) {}

        return back()->with('success', 'Bewertung eingereicht. Sie wird nach Prüfung freigeschaltet.');
    }

    /**
     * Der/die Bewertende bearbeitet die eigene Bewertung.
     * Eine bereits veröffentlichte Bewertung geht dadurch wieder auf 'pending'.
     */
    public function update(Request $request, Review $review)
    {
        $user = $request->user();

        if ($review->reviewer_user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'stars'   => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000', new CleanReviewText()],
        ]);

        $review->update([
            'stars'            => $data['stars'],
            'comment'          => $data['comment'] ?? null,
            'status'           => 'pending',     // erneute Prüfung erforderlich
            'rejection_reason' => null,
            'moderated_at'     => null,
            'reviewed_at'      => now(),
        ]);

        try {
            $review->profile->user->notify(new NewReviewNotification($review->profile, $review->stars));
        } catch (\Throwable $e) {}

        return back()->with('success', 'Bewertung aktualisiert. Sie wird erneut geprüft.');
    }

    /**
     * Öffentliche Antwort der Inserentin – geht ebenfalls zuerst auf 'pending'.
     */
    public function reply(Request $request, Review $review)
    {
        $user = $request->user();

        if ($review->profile->user_id !== $user->id) {
            abort(403);
        }

        // Antworten nur auf freigeschaltete Bewertungen
        if ($review->status !== 'approved') {
            return back()->with('error', 'Auf diese Bewertung kann (noch) nicht geantwortet werden.');
        }

        $data = $request->validate([
            'reply' => ['required', 'string', 'max:500', new CleanReviewText()],
        ]);

        $review->update([
            'inserent_reply'         => $data['reply'],
            'reply_status'           => 'pending',
            'reply_rejection_reason' => null,
            'reply_submitted_at'     => now(),
            'reply_moderated_at'     => null,
        ]);

        return back()->with('success', 'Antwort eingereicht. Sie wird nach Prüfung freigeschaltet.');
    }

    /**
     * Die Inserentin meldet eine Bewertung als problematisch (kein Löschen/Ausblenden).
     */
    public function report(Request $request, Review $review)
    {
        $user = $request->user();

        if ($review->profile->user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        Report::create([
            'reporter_user_id' => $user->id,
            'target_type'      => 'review',
            'target_id'        => $review->id,
            'reason'           => $data['reason'],
            'status'           => 'open',
        ]);

        return back()->with('success', 'Bewertung gemeldet. Ein Administrator prüft sie.');
    }
}
