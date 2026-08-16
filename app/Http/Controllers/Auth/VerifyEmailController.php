<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    /**
     * Verifiziert die E-Mail-Adresse über den signierten Link aus der Bestätigungsmail.
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->target($request))
                ->with('success', 'Deine E-Mail-Adresse ist bereits bestätigt.');
        }

        $request->fulfill(); // setzt email_verified_at und feuert das Verified-Event

        return redirect()->intended($this->target($request))
            ->with('success', 'E-Mail-Adresse bestätigt – dein Konto ist jetzt aktiv!');
    }

    private function target(EmailVerificationRequest $request): string
    {
        return $request->user()->role === 'inserent'
            ? route('inserat.profile.edit')
            : route('home');
    }
}
