<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $subscriptions = $user->platformSubscriptions()
            ->with('profile:id,slug,display_name,subscription_price_chf')
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'status'     => $s->status,
                'amount_chf' => $s->amount_chf,
                'profile'    => [
                    'slug'         => $s->profile->slug,
                    'display_name' => $s->profile->display_name,
                ],
            ]);

        $favoritesCount = $user->favorites()->count();

        return inertia('Member/Dashboard', [
            'subscriptions'  => $subscriptions,
            'favoritesCount' => $favoritesCount,
        ]);
    }
}
