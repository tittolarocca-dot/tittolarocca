<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Inhaltsregeln für Bewertungen und Antworten:
 *  - keine Links / Werbung
 *  - keine E-Mail-Adressen
 *  - keine Telefonnummern
 *  - keine Adressen (Strasse + Hausnummer)
 *  - keine Beleidigungen / Drohungen / diskriminierenden Begriffe
 *
 * Best-effort-Heuristik; die endgültige Kontrolle erfolgt durch die
 * Admin-Freischaltung.
 */
class CleanReviewText implements ValidationRule
{
    /** Beispielhafte Blockliste (klein gehalten, erweiterbar). */
    private const BLOCKLIST = [
        'hurensohn', 'fotze', 'schlampe', 'nutte', 'wichser', 'arschloch',
        'missgeburt', 'schwuchtel', 'neger', 'kanake', 'schwein',
        'bitch', 'whore', 'slut', 'faggot', 'nigger', 'cunt',
        'umbringen', 'abstechen', 'vergewaltig',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $text = (string) $value;
        if (trim($text) === '') {
            return;
        }

        $lower = mb_strtolower($text);

        // E-Mail-Adressen (vor der Link-Prüfung, für die genauere Meldung)
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', $text)) {
            $fail('Bitte keine E-Mail-Adressen angeben.');
            return;
        }

        // Links / URLs / Domains
        if (preg_match('#(https?://|www\.|\b[\w-]+\.(?:ch|com|net|org|de|at|io|info|biz|shop|xyz|link|to|me)\b)#i', $text)) {
            $fail('Bitte keine Links oder Webadressen in der Bewertung.');
            return;
        }

        // Telefonnummern: 9+ Ziffern in einer Folge (Datumsangaben mit 8 Ziffern bleiben erlaubt)
        $digits = preg_replace('/\D+/', '', $text);
        if (preg_match('/(?:\+?\d[\s./\-()]?){7,}/', $text) && strlen($digits) >= 9) {
            $fail('Bitte keine Telefonnummern oder Ziffernfolgen angeben.');
            return;
        }

        // Adressen: Strassenname + Hausnummer (z. B. "Bahnhofstrasse 12")
        if (preg_match('/\b[\p{L}.\-]{3,}(?:strasse|straße|str\.?|weg|gasse|platz|allee|ring)\s+\d+/iu', $text)) {
            $fail('Bitte keine Adressen angeben.');
            return;
        }

        // Beleidigungen / Drohungen / diskriminierende Begriffe
        foreach (self::BLOCKLIST as $bad) {
            if (str_contains($lower, $bad)) {
                $fail('Der Text enthält unzulässige Beleidigungen oder diskriminierende Inhalte.');
                return;
            }
        }
    }
}
