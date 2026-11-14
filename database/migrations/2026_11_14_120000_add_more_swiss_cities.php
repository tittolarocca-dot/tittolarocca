<?php

use App\Models\City;
use Illuminate\Database\Migrations\Migration;

/**
 * Erweitert die Stadt-Auswahl um weitere wichtige Schweizer Städte
 * (Westschweiz, Mittelland, Ostschweiz, Tessin, Agglo Zürich).
 *
 * Idempotent (firstOrCreate nach Slug): läuft beim Deploy via `php artisan
 * migrate` und auch bei frischen Installationen. Jede Stadt mit Kanton +
 * einzigartigem Intro-Text (SEO: kein Duplicate Content). Über das generische
 * /sex-{stadt}-Routing erhält jede Stadt automatisch ihre Landingpages +
 * Sitemap-Einträge.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cities = [
            // Westschweiz
            ['Neuchâtel', 'NE', 'neuchatel', 'Neuchâtel am gleichnamigen See in der Westschweiz findest du auf booklola.ch mit aktuellen Escort- und Begleitinseraten aus der Stadt und der Region. Alle Profile mit Fotos, nach Kategorie filterbar und mit direktem, diskretem Kontakt.'],
            ['Fribourg', 'FR', 'fribourg', 'Fribourg/Freiburg, die zweisprachige Zähringerstadt, findest du auf booklola.ch mit Escort-, Begleit- und Erotikinseraten aus der Stadt und der Region Freiburg. Mit Fotos, filterbar und diskret kontaktierbar.'],
            ['Sion', 'VS', 'sion', 'Sion/Sitten im Herzen des Wallis bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und dem Rhonetal. Alle Profile mit Fotos, nach Kategorie sortierbar und mit direktem, diskretem Kontakt.'],
            ['Montreux', 'VD', 'montreux', 'Montreux an der Waadtländer Riviera am Genfersee findest du auf booklola.ch mit Escort-, Begleit- und Erotikinseraten aus der Stadt und der Region. Mit Fotos, filterbar und diskret kontaktierbar.'],
            ['Vevey', 'VD', 'vevey', 'Vevey am Genfersee, Nachbarstadt von Montreux, bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Riviera. Alle Profile mit Fotos, nach Kategorie filterbar und mit diskretem Kontakt.'],
            ['Yverdon-les-Bains', 'VD', 'yverdon', 'Yverdon-les-Bains am Neuenburgersee findest du auf booklola.ch mit Escort- und Begleitinseraten aus der Stadt und dem Nordwaadtland. Mit Fotos, filterbar und diskret direkt kontaktierbar.'],
            ['Bulle', 'FR', 'bulle', 'Bulle im Greyerzerland (Kanton Freiburg) bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Region Gruyère. Alle Profile mit Fotos, nach Kategorie filterbar und mit diskretem Kontakt.'],

            // Mittelland / Nordwestschweiz
            ['Aarau', 'AG', 'aarau', 'Aarau, die Hauptstadt des Kantons Aargau, findest du auf booklola.ch mit Escort-, Begleit- und Erotikinseraten aus der Stadt und der Region. Mit Fotos, filterbar und diskret kontaktierbar.'],
            ['Baden', 'AG', 'baden', 'Baden im Aargau, bekannt für seine Thermen, bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Region Ostaargau. Alle Profile mit Fotos, nach Kategorie filterbar und mit direktem, diskretem Kontakt.'],
            ['Olten', 'SO', 'olten', 'Olten, der Eisenbahnknotenpunkt im Kanton Solothurn, findest du auf booklola.ch mit Escort- und Begleitinseraten aus der Stadt und der Region. Mit Fotos, filterbar und diskret kontaktierbar.'],
            ['Solothurn', 'SO', 'solothurn', 'Solothurn, die Barockstadt an der Aare, bietet auf booklola.ch aktuelle Escort-, Begleit- und Erotikinserate aus der Stadt und der Region. Alle Profile mit Fotos, nach Kategorie sortierbar und mit diskretem Kontakt.'],
            ['Schaffhausen', 'SH', 'schaffhausen', 'Schaffhausen am Rhein, nahe dem Rheinfall, findest du auf booklola.ch mit Escort- und Begleitinseraten aus der Stadt und der Region. Mit Fotos, filterbar und diskret direkt kontaktierbar.'],
            ['Pratteln', 'BL', 'pratteln', 'Pratteln bei Basel (Baselland) gehört zur Agglomeration Basel. Auf booklola.ch findest du Escort- und Begleitinserate aus Pratteln und der Region – mit Fotos, nach Kategorie filterbar und diskret kontaktierbar.'],
            ['Spreitenbach', 'AG', 'spreitenbach', 'Spreitenbach im Limmattal (Aargau) liegt an der Grenze zum Kanton Zürich. Auf booklola.ch findest du Escort- und Begleitinserate aus Spreitenbach und der Region – mit Fotos, filterbar und mit diskretem Kontakt.'],

            // Ostschweiz
            ['Kreuzlingen', 'TG', 'kreuzlingen', 'Kreuzlingen am Bodensee, direkt an Konstanz grenzend, bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Thurgauer Region. Alle Profile mit Fotos, filterbar und diskret kontaktierbar.'],
            ['Frauenfeld', 'TG', 'frauenfeld', 'Frauenfeld, die Hauptstadt des Kantons Thurgau, findest du auf booklola.ch mit Escort-, Begleit- und Erotikinseraten aus der Stadt und der Region. Mit Fotos, nach Kategorie filterbar und mit diskretem Kontakt.'],
            ['Wil', 'SG', 'wil', 'Wil in der Ostschweiz (Kanton St. Gallen) bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Region. Alle Profile mit Fotos, filterbar und diskret direkt kontaktierbar.'],
            ['Rapperswil-Jona', 'SG', 'rapperswil-jona', 'Rapperswil-Jona am oberen Zürichsee, die Rosenstadt, findest du auf booklola.ch mit Escort- und Begleitinseraten aus der Stadt und der Region. Mit Fotos, nach Kategorie filterbar und mit diskretem Kontakt.'],

            // Tessin
            ['Bellinzona', 'TI', 'bellinzona', 'Bellinzona, die Burgenstadt und Tessiner Kantonshauptstadt, bietet auf booklola.ch aktuelle Escort-, Begleit- und Erotikinserate aus der Stadt und der Region. Alle Profile mit Fotos, filterbar und mit direktem, diskretem Kontakt.'],
            ['Locarno', 'TI', 'locarno', 'Locarno am Lago Maggiore im sonnigen Tessin findest du auf booklola.ch mit Escort- und Begleitinseraten aus der Stadt und der Region. Mit Fotos, nach Kategorie filterbar und diskret kontaktierbar.'],

            // Agglomeration Zürich
            ['Dübendorf', 'ZH', 'duebendorf', 'Dübendorf im Glattal (Kanton Zürich) gehört zur Agglomeration Zürich. Auf booklola.ch findest du Escort- und Begleitinserate aus Dübendorf und der Region – mit Fotos, filterbar und mit diskretem Kontakt.'],
            ['Uster', 'ZH', 'uster', 'Uster im Zürcher Oberland bietet auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Region. Alle Profile mit Fotos, nach Kategorie filterbar und diskret direkt kontaktierbar.'],
            ['Dietikon', 'ZH', 'dietikon', 'Dietikon im Limmattal (Kanton Zürich) liegt zwischen Zürich und Baden. Auf booklola.ch findest du Escort- und Begleitinserate aus Dietikon und der Region – mit Fotos, filterbar und mit direktem, diskretem Kontakt.'],
        ];

        // Hinten anhängen, ohne die Reihenfolge bestehender Städte zu verschieben.
        $sort = (int) City::max('sort_order') + 1;

        foreach ($cities as $i => [$name, $canton, $slug, $intro]) {
            City::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'canton' => $canton, 'is_active' => true, 'sort_order' => $sort + $i]
            );
            City::where('slug', $slug)->whereNull('intro_text')->update(['intro_text' => $intro]);
        }
    }

    public function down(): void
    {
        $slugs = [
            'neuchatel', 'fribourg', 'sion', 'montreux', 'vevey', 'yverdon', 'bulle',
            'aarau', 'baden', 'olten', 'solothurn', 'schaffhausen', 'pratteln', 'spreitenbach',
            'kreuzlingen', 'frauenfeld', 'wil', 'rapperswil-jona', 'bellinzona', 'locarno',
            'duebendorf', 'uster', 'dietikon',
        ];
        foreach ($slugs as $slug) {
            $city = City::where('slug', $slug)->first();
            if ($city && ! \App\Models\Profile::where('city_id', $city->id)->exists()) {
                $city->delete();
            }
        }
    }
};
