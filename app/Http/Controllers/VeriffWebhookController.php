<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Empfängt die Veriff-Entscheidung und aktualisiert NUR Status + Zeitstempel.
 * Es werden keine Ausweisbilder, Selfies oder biometrischen Daten gespeichert.
 *
 * Sicherheit:
 *  - x-auth-client wird gegen den VERIFF_API_KEY geprüft
 *  - x-hmac-signature wird als HMAC-SHA256 über den ROH-Body geprüft
 *  - ohne konfigurierte Keys werden Webhooks abgewiesen (kein offener Endpunkt)
 *  - idempotent: mehrfach gesendete Webhooks ändern nichts erneut
 */
class VeriffWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = (string) config('services.veriff.secret');
        $apiKey = (string) config('services.veriff.api_key');

        // Ohne konfigurierte Keys: keine Verifikation möglich → ablehnen.
        if ($secret === '' || $apiKey === '') {
            abort(401);
        }

        // 1) API-Key-Header prüfen (x-auth-client == öffentlicher API-Key)
        if (! hash_equals($apiKey, (string) $request->header('x-auth-client'))) {
            abort(401);
        }

        // 2) HMAC-SHA256-Signatur gegen den ROH-Request-Body prüfen
        $signature = (string) $request->header('x-hmac-signature');
        $expected  = hash_hmac('sha256', $request->getContent(), $secret);
        if ($signature === '' || ! hash_equals($expected, $signature)) {
            abort(401);
        }

        $v         = $request->input('verification', []);
        $profileId = $v['vendorData'] ?? null;
        $sessionId = $v['id'] ?? null;

        $profile = $profileId
            ? Profile::find($profileId)
            : ($sessionId ? Profile::where('veriff_session_id', $sessionId)->first() : null);

        if (! $profile) {
            return response()->json(['ok' => true]);
        }

        $decision = $v['decision'] ?? $v['status'] ?? null;

        $target = match ($decision) {
            'approved'               => 'approved',
            'declined'               => 'rejected',
            'resubmission_requested' => 'pending',
            'expired', 'abandoned'   => 'not_requested',
            default                  => null,
        };

        if ($target === null) {
            Log::info('Veriff Webhook: unbekannte decision', ['decision' => $decision]);
            return response()->json(['ok' => true]);
        }

        // 3) Idempotenz: identischer Status → keine erneute Änderung
        if ($profile->identity_verification_status === $target) {
            return response()->json(['ok' => true, 'idempotent' => true]);
        }

        switch ($target) {
            case 'approved':
                $profile->update([
                    'identity_verification_status' => 'approved',
                    // Zeitstempel nur einmal setzen (idempotent)
                    'identity_verified_at'         => $profile->identity_verified_at ?? now(),
                    'age_verified_at'              => $profile->age_verified_at ?? now(),
                    'identity_rejected_reason'     => null,
                ]);
                break;

            case 'rejected':
                $profile->update([
                    'identity_verification_status' => 'rejected',
                    'identity_rejected_reason'     => 'Die Prüfung wurde von Veriff abgelehnt.',
                ]);
                break;

            case 'pending':
                $profile->update(['identity_verification_status' => 'pending']);
                break;

            case 'not_requested':
                $profile->update(['identity_verification_status' => 'not_requested']);
                break;
        }

        return response()->json(['ok' => true]);
    }
}
