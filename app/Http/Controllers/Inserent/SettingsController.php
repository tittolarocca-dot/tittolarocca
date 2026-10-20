<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** Unterseite „Einstellungen": Geoblocking + Gefahrenzone (deaktivieren/löschen). */
    public function index(Request $request)
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return redirect()->route('inserat.profile.edit')->with('error', 'Bitte erstelle zuerst dein Profil.');
        }

        return inertia('Inserent/Settings', [
            'profile'   => $profile,
            'countries' => config('countries', []),
            'isActive'  => $profile->isActive(),
        ]);
    }
}
