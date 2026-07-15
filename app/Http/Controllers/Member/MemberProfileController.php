<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Http\Request;

class MemberProfileController extends Controller
{
    public function show(Request $request, User $user)
    {
        // Deaktivierte Konten sind nicht öffentlich sichtbar
        if ($user->deactivated_at) {
            abort(404);
        }

        $authUser = $request->user();

        if ($authUser && $authUser->id !== $user->id) {
            UserVisit::updateOrCreate(
                ['visited_user_id' => $user->id, 'visitor_user_id' => $authUser->id],
                ['last_visited_at' => now()]
            );
        }

        $user->loadMissing('city:id,name,canton');

        return inertia('Member/MemberProfile', [
            'member' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'member_since' => $user->created_at->format('d.m.Y'),
                'gender'       => $user->gender,
                'age'          => $user->age,
                'height_cm'    => $user->height_cm,
                'weight_kg'    => $user->weight_kg,
                'city'         => $user->city?->name,
                'languages'    => $user->languages ?? [],
                'smoking'      => $user->smoking,
                'bio'          => $user->bio,
                'preferences'  => $user->preferences,
            ],
        ]);
    }
}
