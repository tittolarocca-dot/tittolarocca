<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Ermittelt das Land des Besuchers (ISO-3166-1 alpha-2) für das Geoblocking.
 *
 * Standard: Cloudflare-Header „CF-IPCountry" (kostenlos, wenn die Seite hinter
 * Cloudflare läuft). Alternativ ein per Config gesetzter Header. Ohne
 * Erkennung wird null zurückgegeben → es wird nichts blockiert (fail-open).
 */
class VisitorCountry
{
    public function for(Request $request): ?string
    {
        $cf = $request->header('CF-IPCountry');
        if ($this->valid($cf)) {
            return strtoupper($cf);
        }

        $header = config('features.geo_country_header');
        if ($header && $this->valid($alt = $request->header($header))) {
            return strtoupper($alt);
        }

        return null;
    }

    private function valid(?string $code): bool
    {
        return $code !== null && strlen($code) === 2 && ctype_alpha($code) && strtoupper($code) !== 'XX';
    }
}
