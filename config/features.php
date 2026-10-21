<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Launch-Modus
    |--------------------------------------------------------------------------
    | Wenn true: keine echten Zahlungen, keine Preise, Inserate gratis,
    | Push über kostenlose Launch-Credits. Wenn false: Normalbetrieb mit
    | späterer Monetarisierung (Preise/Abos/Payment wieder aktiv).
    */
    'launch_mode' => env('LAUNCH_MODE', true),

    // Anzahl Launch-Credits, die eine neue Inserentin einmalig erhält.
    'launch_credits_initial' => (int) env('LAUNCH_CREDITS_INITIAL', 10),

    // Kosten eines Push in Credits (Launch-Modus).
    'push_credit_cost' => (int) env('PUSH_CREDIT_COST', 1),

    // Einmaliger Bonus, wenn eine Inserentin ihre private Galerie im Launch freigibt.
    'private_gallery_bonus' => (int) env('PRIVATE_GALLERY_BONUS', 3),

    // Laufzeit (Tage), für die ein Inserat im Launch-Modus kostenlos aktiv ist.
    'launch_listing_days' => (int) env('LAUNCH_LISTING_DAYS', 30),

    // Geoblocking: HTTP-Header, aus dem das Besucherland gelesen wird.
    // Standard nutzt Cloudflares "CF-IPCountry". Für andere Setups hier den
    // passenden Header setzen (z. B. wenn ein anderer Proxy das Land liefert).
    'geo_country_header' => env('GEO_COUNTRY_HEADER'),

    // Veriff (Identitäts-/Altersprüfung) im Inserenten-Dashboard anzeigen.
    // false = Veriff-Block ausgeblendet (Standard, solange unentschieden).
    'veriff_enabled' => (bool) env('VERIFF_ENABLED', false),

    // Aufbewahrung der sensiblen 18+ Prüfdateien (Ausweis + Video) NACH
    // erfolgreicher Freigabe, in Tagen. Leer / 0 = KEIN automatisches Löschen
    // (Betreiber entscheidet bewusst; kein erfundenes Default). Ist ein Wert
    // gesetzt, dürfen die Dateien nach reviewed_at + N Tagen sicher gelöscht
    // werden – der Verifizierungs-Status (verified) bleibt erhalten.
    // Gelöscht wird erst, wenn der Command `age-verifications:purge` läuft.
    'age_verification_retention_days' => env('AGE_VERIFICATION_RETENTION_DAYS') !== null
        ? (int) env('AGE_VERIFICATION_RETENTION_DAYS')
        : null,

    // Max. Dateigrößen (MB) für die 18+ Uploads.
    'age_verification_max_document_mb' => (int) env('AGE_VERIFICATION_MAX_DOCUMENT_MB', 15),
    'age_verification_max_video_mb'    => (int) env('AGE_VERIFICATION_MAX_VIDEO_MB', 80),

    // Pre-Launch: true = Gäste sehen nur /inserieren + Anmelde-Strecke,
    // der Marktplatz ist versteckt (nur für eingeloggte Nutzer/Admin).
    // false = Seite komplett öffentlich (Voll-Launch).
    'prelaunch_mode' => (bool) env('PRELAUNCH_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Credit-Pakete (Vorbereitung für später – KEIN aktiver Checkout)
    |--------------------------------------------------------------------------
    | 1 Credit = CHF 1. Wird erst nach LAUNCH_MODE=false + Payment-Anbindung
    | verwendet. Dient aktuell nur als Datengrundlage/Referenz.
    */
    'credit_packages' => [
        ['credits' => 20,  'price_chf' => 20],
        ['credits' => 50,  'price_chf' => 50],
        ['credits' => 100, 'price_chf' => 100],
    ],
];
