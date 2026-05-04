<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:15360',
        ], [
            'photo.required' => 'Bitte wähle ein Foto aus.',
            'photo.image'    => 'Die Datei muss ein Bild sein.',
            'photo.mimes'    => 'Erlaubte Formate: JPG, PNG.',
            'photo.max'      => 'Das Foto darf max. 15 MB gross sein.',
        ]);

        $profile = $request->user()->profile;

        if (!$profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        if ($profile->verification_photo) {
            Storage::disk('local')->delete($profile->verification_photo);
        }

        $path = $request->file('photo')->storeAs(
            'verifications',
            Str::uuid() . '.' . $request->file('photo')->getClientOriginalExtension(),
            'local'
        );

        $profile->update([
            'verification_status'          => 'pending',
            'verification_photo'           => $path,
            'verification_rejected_reason' => null,
            'verification_submitted_at'    => now(),
            'verification_reviewed_at'     => null,
        ]);

        return back()->with('success', 'Verifikationsfoto eingereicht! Wir prüfen es innerhalb von 24 Stunden.');
    }
}
