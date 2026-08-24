<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pre-Launch-Modus: Solange config('features.prelaunch_mode') an ist, sehen
 * NICHT eingeloggte Gäste nur die Werbeseite /inserieren und die Anmelde-
 * Strecke. Alle Marktplatz-Seiten (Startseite, Profile, Suche, Städte,
 * Kategorien, Clubs …) werden für Gäste auf /inserieren umgeleitet.
 *
 * Eingeloggte Nutzer (Admin, Inserent:innen, Mitglieder) haben vollen Zugriff.
 */
class PreLaunch
{
    /** Pfad-Präfixe, die Gäste im Pre-Launch weiterhin erreichen dürfen. */
    private const GUEST_ALLOWED = [
        'inserieren',    // Werbe-Landingpage
        'registrieren',  // Registrierung (GET + POST)
        'login',         // Login (GET + POST)
        'logout',
        'email',         // E-Mail-Bestätigung (/email/verifizieren…)
        'impressum', 'datenschutz', 'agb', // Rechtstexte
        'lang',          // Sprachumschalter
        'media',         // Medien-Streams (Bilder)
        'sitemap.xml',
        'stripe', 'veriff', // Webhooks externer Dienste
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Modus aus → nichts tun
        if (! config('features.prelaunch_mode')) {
            return $next($request);
        }

        // Eingeloggt → voller Zugriff
        if ($request->user()) {
            return $next($request);
        }

        $path = $request->path(); // ohne führenden Slash; '/' bei der Startseite

        // Livewire-Endpunkte (auch mit obfusziertem Prefix, z. B. livewire-xxxx/update)
        // niemals abfangen – sonst schlägt u. a. der Filament-Admin-Login fehl,
        // weil die Authentifizierung erst INNERHALB dieser Anfrage passiert.
        if (str_starts_with($path, 'livewire')) {
            return $next($request);
        }

        foreach (self::GUEST_ALLOWED as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        // Alles andere (Marktplatz) → Gäste auf die Werbeseite
        return redirect()->route('landing.advertise');
    }
}
