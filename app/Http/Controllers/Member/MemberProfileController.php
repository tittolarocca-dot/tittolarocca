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
        $authUser = $request->user();

        if ($authUser && $authUser->id !== $user->id) {
            UserVisit::updateOrCreate(
                ['visited_user_id' => $user->id, 'visitor_user_id' => $authUser->id],
                ['last_visited_at' => now()]
            );
        }

        return inertia('Member/MemberProfile', [
            'member' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'member_since' => $user->created_at->format('d.m.Y'),
            ],
        ]);
    }
}
