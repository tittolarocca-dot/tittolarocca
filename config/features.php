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
