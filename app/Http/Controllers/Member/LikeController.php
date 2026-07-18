<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Toggle: 1 Like pro Member und Inserat.
     * Die Anzahl der Likes = Anzahl Member, die geliked haben.
     */
    public function toggle(Request $request, Profile $profile)
    {
        $user = $request->user();

        if ($user->likes()->where('profile_id', $profile->id)->exists()) {
            $user->likes()->detach($profile->id);
        } else {
            $user->likes()->attach($profile->id);
        }

        return back();
    }
}
