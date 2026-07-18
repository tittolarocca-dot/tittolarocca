<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * Startet eine Veriff-Identitäts-/Alterprüfung.
 * Es werden NUR Session-ID und Status gespeichert – keine Ausweisbilder,
 * Selfies oder biometrischen Daten.
 */
class VeriffController extends Controller
{
    public function start(Request $request)
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        if ($profile->identity_verification_status === 'approved') {
            return back()->with('error', 'Deine Identität ist bereits verifiziert.');
        }

        $apiKey = config('services.veriff.api_key');
        $base   = rtrim((string) config('services.veriff.base_url'), '/');

        if (! $apiKey) {
            return back()->with('error', 'Die Identitätsprüfung (Veriff) ist noch nicht konfiguriert.');
        }

        try {
            $resp = Http::withHeaders(['X-AUTH-CLIENT' => $apiKey])
                ->acceptJson()
                ->post($base . '/v1/sessions', [
                    'verification' => [
                        'callback'   => route('veriff.webhook'),
                        'vendorData' => (string) $profile->id,
                        'person'     => ['firstName' => $profile->display_name],
                    ],
                ]);

            $session = $resp->json('verification');

            if (! $resp->successful() || empty($session['url'])) {
                Log::warning('Veriff: Session konnte nicht erstellt werden', ['status' => $resp->status()]);
                return back()->with('error', 'Verifizierung konnte nicht gestartet werden. Bitte später erneut versuchen.');
            }

            $profile->update([
                'veriff_session_id'            => $session['id'] ?? null,
                'identity_verification_status' => 'pending',
                'identity_rejected_reason'     => null,
                'identity_submitted_at'        => now(),
            ]);

            // Externer Redirect zur Veriff-Seite
            return Inertia::location($session['url']);
        } catch (\Throwable $e) {
            Log::warning('Veriff start error: ' . $e->getMessage());
            return back()->with('error', 'Verifizierung konnte nicht gestartet werden.');
        }
    }
}
