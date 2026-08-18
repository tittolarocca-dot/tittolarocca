<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master-Schalter für die Indexierung
    |--------------------------------------------------------------------------
    | false = die GESAMTE Seite bleibt auf "noindex, nofollow" (Bauphase).
    | true  = öffentliche Seiten werden für Google freigegeben; private
    |         Seiten (Login, Konto, Inserat-Bereich …) bleiben trotzdem
    |         "noindex", weil sie das nie explizit erlauben.
    |
    | Dieser Schalter ist der bewusste "Go-Live für Google": am Ende in der
    | .env auf true setzen (SEO_INDEXABLE=true).
    */
    'indexable' => (bool) env('SEO_INDEXABLE', false),

    // Marken-/Seitenname – wird an Seitentitel angehängt.
    'site_name' => env('SEO_SITE_NAME', 'booklola.ch'),

    // Standard-Description, falls eine Seite keine eigene setzt.
    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'booklola.ch – Erotik, Escort & Begleitung in der Schweiz. Inserate mit Fotos nach Stadt und Kategorie.'
    ),
];
