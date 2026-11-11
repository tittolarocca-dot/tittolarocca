<?php

use App\Models\City;
use Illuminate\Database\Migrations\Migration;

/**
 * Fügt die Städte Zug und Emmenbrücke zur Auswahl hinzu (idempotent).
 *
 * Städte werden sonst nur im DatabaseSeeder gepflegt; der läuft produktiv aber
 * nicht beim Deploy (nur `migrate`). Darum hier als Data-Migration, damit die
 * neuen Städte per `php artisan migrate` auf dem Server erscheinen, ohne den
 * kompletten Seeder (inkl. Admin-User etc.) laufen zu lassen.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cities = [
            [
                'name'   => 'Zug',
                'canton' => 'ZG',
                'slug'   => 'zug',
                'intro'  => 'Zug am Zugersee in der Zentralschweiz findest du auf booklola.ch mit aktuellen Escort-, Begleit- und Erotikinseraten aus der Stadt Zug und der Region. Alle Profile mit Fotos, nach Kategorie filterbar und mit direktem, diskretem Kontakt.',
            ],
            [
                'name'   => 'Emmenbrücke',
                'canton' => 'LU',
                'slug'   => 'emmenbruecke',
                'intro'  => 'Emmenbrücke bei Luzern gehört zur Agglomeration Luzern im Kanton Luzern. Auf booklola.ch findest du Escort- und Begleitinserate aus Emmenbrücke und der Region – mit Fotos, nach Kategorie filterbar und diskret direkt kontaktierbar.',
            ],
        ];

        // Hinten anhängen, ohne die Reihenfolge bestehender Städte zu verschieben.
        $sort = (int) City::max('sort_order') + 1;

        foreach ($cities as $i => $c) {
            City::firstOrCreate(
                ['slug' => $c['slug']],
                [
                    'name'       => $c['name'],
                    'canton'     => $c['canton'],
                    'is_active'  => true,
                    'sort_order' => $sort + $i,
                ]
            );

            // intro_text nur setzen, wenn noch leer (überschreibt keine Redaktion).
            City::where('slug', $c['slug'])
                ->whereNull('intro_text')
                ->update(['intro_text' => $c['intro']]);
        }
    }

    public function down(): void
    {
        // Nur löschen, wenn keine Inserate daran hängen (sonst FK-Schutz greift
        // ohnehin). Rollback ist für diese Data-Migration unüblich.
        foreach (['zug', 'emmenbruecke'] as $slug) {
            $city = City::where('slug', $slug)->first();
            if ($city && ! \App\Models\Profile::where('city_id', $city->id)->exists()) {
                $city->delete();
            }
        }
    }
};
