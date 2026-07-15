<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
                'avatar_url'   => ($user->avatar_path && $user->avatar_status === 'approved')
                    ? route('mitglied.avatar', $user->id) . '?v=' . ($user->updated_at?->timestamp ?? 1)
                    : null,
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

    /** Profilfoto ausliefern (serverseitig verarbeitetes WebP). */
    public function avatar(Request $request, User $user)
    {
        if ($user->deactivated_at) {
            abort(404);
        }

        $disk = Storage::disk('local');
        if (! $user->avatar_path || ! $disk->exists($user->avatar_path)) {
            abort(404);
        }

        // Öffentlich nur nach Freigabe. Eigentümer:in und Admins/Moderatoren
        // dürfen auch ausstehende/abgelehnte Fotos sehen (Vorschau/Moderation).
        $viewer = $request->user();
        $maySeeUnapproved = $viewer && (
            $viewer->id === $user->id || in_array($viewer->role, ['admin', 'moderator'], true)
        );
        if ($user->avatar_status !== 'approved' && ! $maySeeUnapproved) {
            abort(404);
        }

        $mime = str_ends_with($user->avatar_path, '.webp') ? 'image/webp' : 'image/jpeg';

        return response($disk->get($user->avatar_path), 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
