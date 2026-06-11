<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ProfileVisit;
use Illuminate\Http\Request;

class ProfileVisitorController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;

        $visitors = collect();

        if ($profile) {
            $visitors = ProfileVisit::where('profile_owner_user_id', $user->id)
                ->with(['visitor:id,name'])
                ->orderByDesc('last_visited_at')
                ->get()
                ->map(fn($visit) => [
                    'name'            => $visit->visitor->name,
                    'last_visited_at' => $visit->last_visited_at->diffForHumans(),
                ]);
        }

        return inertia('Member/ProfileVisitors', [
            'visitors'    => $visitors,
            'hasProfile'  => (bool) $profile,
        ]);
    }
}
