<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PrivateGalleryRequest;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\NewGalleryRequestNotification;
use Illuminate\Http\Request;

class GalleryRequestController extends Controller
{
    /**
     * Ein Mitglied fragt Zugang zur privaten Galerie einer Inserentin an.
     * Die Inserentin gibt später pro Person frei (siehe Inserent\GalleryRequestController).
     */
    public function store(Request $request, Profile $profile)
    {
        $user = $request->user();

        // Eigenes Profil oder bereits freigegeben → nichts zu tun.
        if ($profile->user_id === $user->id) {
            return back();
        }

        $existing = PrivateGalleryRequest::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->status === 'approved') {
            return back()->with('success', 'Du hast bereits Zugang zu diesen Bildern.');
        }

        // Neu anlegen oder eine abgelehnte/alte Anfrage wieder auf „pending" setzen.
        $wasPending = $existing && $existing->status === 'pending';

        PrivateGalleryRequest::updateOrCreate(
            ['profile_id' => $profile->id, 'user_id' => $user->id],
            ['status' => 'pending', 'responded_at' => null],
        );

        // Inserentin per E-Mail benachrichtigen (nicht bei reiner Wiederholung derselben offenen Anfrage).
        if (! $wasPending) {
            $owner = User::find($profile->user_id);
            $owner?->notify(new NewGalleryRequestNotification($profile, $user->name));
        }

        return back()->with('success', 'Anfrage gesendet. Du wirst benachrichtigt, sobald freigegeben wird.');
    }
}
