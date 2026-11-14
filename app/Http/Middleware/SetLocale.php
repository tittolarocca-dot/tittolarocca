<?php

namespace App\Http\Middleware;

use App\Support\LocaleDetector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['de', 'en', 'fr', 'it', 'es', 'hu', 'ro'];
    public const DEFAULT    = 'de';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        $response = $next($request);

        // Content-Negotiation-Signal für Caches/Google: Sprache variiert je
        // Accept-Language (SEO-sauber, kein Cloaking). Bestehende Vary-Werte
        // (z. B. Accept-Encoding) bleiben erhalten.
        if (method_exists($response, 'setVary')) {
            $response->setVary(
                array_values(array_unique(array_merge($response->getVary(), ['Accept-Language']))),
                true
            );
        }

        return $response;
    }

    /**
     * Prioritäten:
     *  1. Expliziter Routen-Parameter (/lang/{locale})          – aktive Aktion
     *  2. Manuelle Wahl der eingeloggten Nutzer:in (users.locale) – dauerhaft, geräteübergreifend
     *  3. Manuelle Wahl als Cookie (locale)                      – dauerhaft pro Gerät
     *  4. Bereits in DIESER Session gesetzte/erkannte Sprache     – stabil innerhalb der Session
     *  5. Automatische Erkennung (nur beim 1. Besuch): Accept-Language → CH-Region → Standard
     *
     * Eine MANUELLE Wahl (Cookie/User) wird vor der Erkennung geprüft und daher
     * NIE automatisch überschrieben. Die Erkennung landet nur in der Session.
     */
    private function resolve(Request $request): string
    {
        $valid = fn ($l) => is_string($l) && in_array($l, self::SUPPORTED, true) ? $l : null;

        if ($l = $valid($request->route('locale'))) {
            return $l;
        }
        if (($u = $request->user()) && ($l = $valid($u->locale ?? null))) {
            return $l;
        }
        if ($l = $valid($request->cookie('locale'))) {
            return $l;
        }
        if ($l = $valid($request->session()->get('locale'))) {
            return $l;
        }

        return LocaleDetector::detect(
            $request->header('Accept-Language'),
            $request->header(config('features.geo_region_header', 'CF-Region-Code')),
            $request->header(config('features.geo_country_header') ?: 'CF-IPCountry'),
            self::SUPPORTED,
            self::DEFAULT,
        );
    }
}
