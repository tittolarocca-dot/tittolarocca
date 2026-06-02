<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->where('status', 'active')
            ->with(['publicMedia' => fn($q) => $q->where('type', 'image')->orderBy('sort_order')])
            ->get()
            ->map(fn($profile) => [
                'slug'                => $profile->slug,
                'display_name'        => $profile->display_name,
                'cover_url'           => $profile->publicMedia->first()?->url,
                'verification_status' => $profile->verification_status,
            ]);

        return inertia('Member/Favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function toggle(Request $request, Profile $profile)
    {
        $user = $request->user();

        if ($user->favorites()->where('profile_id', $profile->id)->exists()) {
            $user->favorites()->detach($profile->id);
            $isFavorited = false;
        } else {
            $user->favorites()->attach($profile->id);
            $isFavorited = true;
        }

        return back()->with('isFavorited', $isFavorited);
    }
}
