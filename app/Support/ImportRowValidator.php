<?php

namespace App\Support;

/**
 * Prüft/normalisiert EINE CSV-Zeile für den Admin-Bulk-Import – framework-frei
 * und isoliert testbar. Erhält alle Nachschlagedaten als Arrays (keine DB),
 * damit die Logik rein und deterministisch bleibt.
 *
 * Status:
 *   ready     – alle Pflichtfelder gültig, keine Dublette → importierbar
 *   duplicate – mögliche Dublette (E-Mail/Profilname existiert) → NICHT automatisch importieren
 *   error     – Pflichtfeld fehlt/ungültig → nicht importierbar
 * (Vorrang: error > duplicate > ready.)
 */
class ImportRowValidator
{
    /**
     * @param array $row CSV-Zeile (assoziativ nach Header)
     * @param array $ctx [
     *   'cities'         => [lowercase name|slug => city_id],
     *   'categories'     => [lowercase name|slug => category_id],
     *   'existingEmails' => [lowercase email => true],
     *   'existingNames'  => [lowercase display_name => true],
     *   'seenEmails'     => [lowercase email => true]  (frühere Zeilen derselben Datei),
     *   'seenNames'      => [lowercase display_name => true],
     * ]
     */
    public static function validate(array $row, array $ctx): array
    {
        $g = fn (string $k) => trim((string) ($row[$k] ?? ''));

        $name        = $g('display_name');
        $email       = mb_strtolower($g('email'));
        $cityRaw     = $g('city');
        $categoryRaw = $g('category');
        $ageRaw      = $g('age');
        $phone       = $g('phone');
        $description = $g('description');
        $languageRaw = $g('language');
        $address     = $g('address');
        $servicesRaw = $g('services');

        $messages = [];

        if (mb_strlen($name) < 2) {
            $messages[] = 'Profilname fehlt oder zu kurz';
        }

        if ($email === '') {
            $messages[] = 'E-Mail fehlt';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $messages[] = 'E-Mail ungültig';
        }

        $cityId = $ctx['cities'][mb_strtolower($cityRaw)] ?? null;
        if ($cityRaw === '') {
            $messages[] = 'Ort fehlt';
        } elseif (! $cityId) {
            $messages[] = "Ort unbekannt: {$cityRaw}";
        }

        $catId = $ctx['categories'][mb_strtolower($categoryRaw)] ?? null;
        if ($categoryRaw === '') {
            $messages[] = 'Rubrik fehlt';
        } elseif (! $catId) {
            $messages[] = "Rubrik unbekannt: {$categoryRaw}";
        }

        // Alter optional – darf leer sein; wenn gesetzt, muss es 18–99 sein.
        $age = null;
        if ($ageRaw !== '') {
            if (! ctype_digit($ageRaw) || (int) $ageRaw < 18 || (int) $ageRaw > 99) {
                $messages[] = 'Alter ungültig (18–99)';
            } else {
                $age = (int) $ageRaw;
            }
        }

        $languages = self::parseLanguages($languageRaw);
        $services  = self::parseServices($servicesRaw, $ctx['tags'] ?? []);

        // Dubletten (nur sinnvoll, wenn die Identitätsfelder selbst gültig sind)
        $dupMsg = [];
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (isset($ctx['existingEmails'][$email]) || isset($ctx['seenEmails'][$email])) {
                $dupMsg[] = 'E-Mail existiert bereits';
            }
        }
        if (mb_strlen($name) >= 2) {
            $nl = mb_strtolower($name);
            if (isset($ctx['existingNames'][$nl]) || isset($ctx['seenNames'][$nl])) {
                $dupMsg[] = 'Profilname existiert bereits';
            }
        }

        if (! empty($messages)) {
            $status = 'error';
            $messages = array_merge($messages, $dupMsg);
        } elseif (! empty($dupMsg)) {
            $status = 'duplicate';
            $messages = $dupMsg;
        } else {
            $status = 'ready';
        }

        return [
            'status'   => $status,
            'messages' => $messages,
            'display'  => ['name' => $name, 'email' => $email, 'city' => $cityRaw, 'phone' => $phone],
            'data'     => [
                'display_name' => $name,
                'email'        => $email,
                'city_id'      => $cityId,
                'category_id'  => $catId,
                'age'          => $age,
                'phone'        => $phone,
                'description'  => $description,
                'languages'    => $languages,
                'address'      => $address !== '' ? $address : null,
                'services'     => $services,   // Array von Tag-IDs
            ],
        ];
    }

    /**
     * "Erotische Massage, GFE; Striptease" → [Tag-IDs] (best effort, optional).
     * Matcht Service-Namen/Slugs gegen die übergebene Tag-Nachschlagetabelle
     * (lowercase name|slug => id). Unbekannte Services werden ignoriert.
     */
    public static function parseServices(string $raw, array $tagsLookup): array
    {
        if ($raw === '' || empty($tagsLookup)) {
            return [];
        }
        $out = [];
        foreach (preg_split('/[,;|]+/', $raw) as $p) {
            $p = mb_strtolower(trim($p));
            if ($p === '') {
                continue;
            }
            $id = $tagsLookup[$p] ?? null;
            if ($id && ! in_array($id, $out, true)) {
                $out[] = $id;
            }
        }
        return $out;
    }

    /** "Deutsch, English" / "de;fr" → ['de','en'] (best effort, optionales Feld). */
    public static function parseLanguages(string $raw): array
    {
        if ($raw === '') {
            return [];
        }
        $map = [
            'deutsch' => 'de', 'german' => 'de', 'allemand' => 'de',
            'english' => 'en', 'englisch' => 'en', 'anglais' => 'en',
            'französisch' => 'fr', 'franzoesisch' => 'fr', 'francais' => 'fr', 'français' => 'fr', 'french' => 'fr',
            'italienisch' => 'it', 'italiano' => 'it', 'italian' => 'it', 'italien' => 'it',
            'spanisch' => 'es', 'español' => 'es', 'espanol' => 'es', 'spanish' => 'es', 'espagnol' => 'es',
        ];
        $out = [];
        foreach (preg_split('/[,;\/|]+/', $raw) as $p) {
            $p = mb_strtolower(trim($p));
            if ($p === '') {
                continue;
            }
            $code = $map[$p] ?? (preg_match('/^[a-z]{2}$/', $p) ? $p : null);
            if ($code && ! in_array($code, $out, true)) {
                $out[] = $code;
            }
        }
        return $out;
    }
}
