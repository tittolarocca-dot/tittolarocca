<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyReviewsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $reviews = $user->writtenReviews()
            ->with('profile:id,slug,display_name')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'stars'      => $r->stars,
                'comment'    => $r->comment,
                'status'     => $r->status,
                'reply'      => $r->reply_status === 'approved' ? $r->inserent_reply : null,
                'created_at' => $r->created_at->format('d.m.Y'),
                'profile'    => $r->profile ? [
                    'slug'         => $r->profile->slug,
                    'display_name' => $r->profile->display_name,
                ] : null,
            ]);

        return inertia('Member/MyReviews', [
            'reviews' => $reviews,
        ]);
    }
}
