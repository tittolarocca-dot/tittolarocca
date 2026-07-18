<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Empfängt die Veriff-Entscheidung und aktualisiert NUR Status + Zeitstempel.
 * Es werden keine Ausweisbilder, Selfies oder biometrischen Daten gespeichert.
 */
class VeriffWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.veriff.secret');

        // Signatur prüfen (HMAC-SHA256 des Roh-Payloads)
        if ($secret) {
            $signature = $request->header('x-hmac-signature');
            $expected  = hash_hmac('sha256', $request->getContent(), $secret);
            if (! $signature || ! hash_equals($expected, $signature)) {
                abort(401);
            }
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

        switch ($decision) {
            case 'approved':
                $profile->update([
                    'identity_verification_status' => 'approved',
                    'identity_verified_at'         => now(),
                    'age_verified_at'              => now(),
                    'identity_rejected_reason'     => null,
                ]);
                break;

            case 'declined':
                $profile->update([
                    'identity_verification_status' => 'rejected',
                    'identity_rejected_reason'     => 'Die Prüfung wurde von Veriff abgelehnt.',
                ]);
                break;

            case 'resubmission_requested':
                $profile->update(['identity_verification_status' => 'pending']);
                break;

            case 'expired':
            case 'abandoned':
                $profile->update(['identity_verification_status' => 'not_requested']);
                break;

            default:
                Log::info('Veriff Webhook: unbekannte decision', ['decision' => $decision]);
        }

        return response()->json(['ok' => true]);
    }
}
