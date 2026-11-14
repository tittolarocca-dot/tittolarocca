<?php

namespace App\Support;

/**
 * Reine (framework-freie) Spracherkennung für den ERSTEN Besuch – nur wenn noch
 * KEINE manuelle Wahl gespeichert ist. Reihenfolge laut Anforderung:
 *
 *   1) Accept-Language (Browser) → erste unterstützte Sprache in der Präferenzliste
 *   2) Schweizer Region (Cloudflare) → Romandie = fr, Ticino = it
 *   3) Standard (de)
 *
 * Bewusst ohne Laravel-Abhängigkeiten, damit die Logik isoliert testbar ist.
 */
class LocaleDetector
{
    /**
     * Erste von Booklola unterstützte Sprache aus dem Accept-Language-Header.
     * Berücksichtigt q-Werte (höchste zuerst; bei Gleichstand bleibt die
     * Reihenfolge erhalten – PHP-Sort ist stabil).
     */
    public static function fromAcceptLanguage(?string $header, array $supported): ?string
    {
        if (! $header) {
            return null;
        }

        $tags = [];
        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $q = 1.0;
            if (preg_match('/;\s*q\s*=\s*([0-9.]+)/i', $part, $m)) {
                $q = (float) $m[1];
            }
            $tag = strtolower(trim(preg_replace('/;.*$/', '', $part)));
            if ($tag === '' || $tag === '*') {
                continue;
            }
            $tags[] = ['tag' => $tag, 'q' => $q];
        }

        usort($tags, fn ($a, $b) => $b['q'] <=> $a['q']); // stabil: gleicher q behält Reihenfolge

        foreach ($tags as $t) {
            $primary = explode('-', $t['tag'])[0];     // de-CH → de
            if (in_array($primary, $supported, true)) {
                return $primary;
            }
        }

        return null;
    }

    /**
     * Sprache aus der Schweizer Region (nur wenn Land = CH). Erwartet einen
     * ISO-3166-2-Subdivisions-Code wie "GE", "VD", "TI" (Cloudflare
     * CF-Region-Code); ein evtl. Präfix "CH-" wird entfernt.
     *
     * Bewusst NUR eindeutige Regionen: Romandie → fr, Ticino → it.
     * Mehrsprachige Kantone (FR/VS/GR) bleiben hier null → fallen auf den
     * Standard; die Browsersprache (Prio 1) hätte sie ohnehin schon abgedeckt.
     */
    public static function fromRegion(?string $regionCode, ?string $country): ?string
    {
        if (strtoupper((string) $country) !== 'CH' || ! $regionCode) {
            return null;
        }

        $r = strtoupper(trim($regionCode));
        if (str_contains($r, '-')) {
            $r = substr($r, strrpos($r, '-') + 1); // "CH-GE" → "GE"
        }

        if (in_array($r, ['GE', 'VD', 'NE', 'JU'], true)) {
            return 'fr';
        }
        if ($r === 'TI') {
            return 'it';
        }

        return null;
    }

    /** Komplette Erkennung mit Fallback-Kette. */
    public static function detect(
        ?string $acceptLanguage,
        ?string $regionCode,
        ?string $country,
        array $supported,
        string $default
    ): string {
        return self::fromAcceptLanguage($acceptLanguage, $supported)
            ?? self::fromRegion($regionCode, $country)
            ?? $default;
    }
}
