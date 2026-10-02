<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /** Einem Profil folgen / entfolgen (getrennt von Like und Favorit). */
    public function toggle(Request $request, Profile $profile)
    {
        $user = $request->user();

        // Ein Profil darf sich nicht selbst folgen.
        if ($profile->user_id === $user->id) {
            return back();
        }

        if ($user->follows()->where('profile_id', $profile->id)->exists()) {
            $user->follows()->detach($profile->id);
            $following = false;
        } else {
            // attach ist dank UNIQUE(follower_user_id, profile_id) gegen Doppelungen sicher
            $user->follows()->syncWithoutDetaching([$profile->id]);
            $following = true;
        }

        return back()->with('isFollowing', $following);
    }
}
