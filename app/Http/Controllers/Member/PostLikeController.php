<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ProfilePost;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    /** Einen Feed-Beitrag liken / unliken (getrennt von Profil-Likes). */
    public function toggle(Request $request, ProfilePost $post)
    {
        $user = $request->user();

        $post->loadMissing('profile');

        // Nur sichtbare Beiträge dürfen geliked werden (serverseitig geprüft).
        abort_unless($post->isVisibleTo($user), 403);

        if ($post->likedBy()->where('user_id', $user->id)->exists()) {
            $post->likedBy()->detach($user->id);
        } else {
            $post->likedBy()->syncWithoutDetaching([$user->id]);
        }

        return back();
    }
}
