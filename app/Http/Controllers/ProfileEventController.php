<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileEvent;
use Illuminate\Http\Request;

/**
 * Nimmt Kontakt-Klicks (WhatsApp/Anruf/Telegram/Website) vom öffentlichen Profil
 * entgegen und protokolliert sie zeitgestempelt für die Inserenten-Statistik.
 * Bewusst „fire-and-forget": antwortet immer mit 204, damit der Klick-Link
 * ohne Verzögerung öffnet. Eigentümer-Klicks werden nicht gezählt.
 */
class ProfileEventController extends Controller
{
    public function store(Request $request, Profile $profile)
    {
        $type = (string) $request->input('type');

        if (! in_array($type, ProfileEvent::CLICK_TYPES, true) || ! $profile->isActive()) {
            return response()->noContent();
        }

        $user = $request->user();

        // Eigentümer:in zählt nicht in die eigene Statistik.
        if ($user && (int) $user->id === (int) $profile->user_id) {
            return response()->noContent();
        }

        // Leichte Entdopplung: gleicher Typ je Session max. 1× / 2 Min
        // (verhindert Doppel-Taps, ohne echte wiederholte Klicks zu verlieren).
        $key  = 'pe_click_' . $profile->id . '_' . $type;
        $last = (int) $request->session()->get($key, 0);
        if (now()->timestamp - $last < 120) {
            return response()->noContent();
        }
        $request->session()->put($key, now()->timestamp);

        ProfileEvent::create([
            'profile_id'      => $profile->id,
            'type'            => $type,
            'visitor_user_id' => $user?->id,
        ]);

        return response()->noContent();
    }
}
