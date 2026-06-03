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

        $favoritesPreview = $user->favorites()
            ->where('status', 'active')
            ->with(['publicMedia' => fn($q) => $q->where('type', 'image')->orderBy('sort_order')])
            ->latest('favorites.created_at')
            ->limit(4)
            ->get()
            ->map(fn($p) => [
                'slug'                => $p->slug,
                'display_name'        => $p->display_name,
                'cover_url'           => $p->publicMedia->first()?->url,
                'verification_status' => $p->verification_status,
            ]);

        return inertia('Member/Dashboard', [
            'subscriptions'    => $subscriptions,
            'favoritesPreview' => $favoritesPreview,
            'favoritesCount'   => $user->favorites()->count(),
        ]);
    }
}
