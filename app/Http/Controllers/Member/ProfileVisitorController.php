<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\UserVisit;
use Illuminate\Http\Request;

class ProfileVisitorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $visitors = UserVisit::where('visited_user_id', $user->id)
            ->with([
                'visitor:id,name',
                'visitor.profile' => fn($q) => $q
                    ->select('id', 'user_id', 'slug', 'display_name', 'city_id', 'age', 'status', 'listing_expires_at')
                    ->with([
                        'city:id,name',
                        'publicMedia' => fn($q) => $q->where('type', 'image')->orderBy('sort_order')->limit(1),
                    ]),
            ])
            ->orderByDesc('last_visited_at')
            ->get()
            ->map(fn($visit) => [
                'name'            => $visit->visitor->name,
                'last_visited_at' => $visit->last_visited_at->diffForHumans(),
                'profile'         => $visit->visitor->profile ? [
                    'slug'         => $visit->visitor->profile->slug,
                    'display_name' => $visit->visitor->profile->display_name,
                    'city'         => $visit->visitor->profile->city?->name,
                    'age'          => $visit->visitor->profile->age,
                    'cover_url'    => data_get($visit->visitor->profile->publicMedia->first(), 'src.thumbnail')
                                        ?? $visit->visitor->profile->publicMedia->first()?->url,
                ] : null,
            ]);

        return inertia('Member/ProfileVisitors', [
            'visitors' => $visitors,
        ]);
    }
}
