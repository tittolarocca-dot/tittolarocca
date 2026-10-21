<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return inertia('Auth/Login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Bitte gib deine E-Mail-Adresse ein.',
            'password.required' => 'Bitte gib dein Passwort ein.',
        ]);

        $this->checkRateLimit($request);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => 'Diese Zugangsdaten sind nicht korrekt.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status === 'blocked') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Dein Konto wurde gesperrt. Bitte kontaktiere den Support.',
            ]);
        }

        // Deaktiviertes Konto durch erneutes Login reaktivieren
        if ($user->deactivated_at) {
            $user->update(['deactivated_at' => null]);
        }

        // Auth-Wechsel: BEWUSST ein voller Browser-Reload (Inertia::location) statt
        // eines SPA-Besuchs. Gründe: CSRF-Token und geteilte Auth-Props (auth.user,
        // Avatar …) ändern sich, und iOS Safari zeichnet nach einem Inertia-POST→
        // Redirect sonst einen unfertigen Frame, bis man den Bildschirm berührt
        // (Seite erscheint erst „verschoben"/ohne Menü). Für Nicht-Inertia-Requests
        // liefert Inertia::location einen normalen 302-Redirect.
        $target = $user->role === 'inserent'
            ? route('inserat.dashboard')
            : route('home');

        return Inertia::location(redirect()->intended($target)->getTargetUrl());
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Ebenfalls voller Reload nach dem Logout (Auth-Status/CSRF ändern sich).
        return Inertia::location(route('home'));
    }

    private function checkRateLimit(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => "Zu viele Versuche. Bitte warte {$seconds} Sekunden.",
            ]);
        }
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }
}
