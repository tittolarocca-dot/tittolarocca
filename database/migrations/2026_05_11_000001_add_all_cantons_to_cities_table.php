<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $cantons = [
            ['name' => 'Zürich',                    'canton' => 'ZH', 'slug' => 'zuerich'],
            ['name' => 'Bern',                       'canton' => 'BE', 'slug' => 'bern'],
            ['name' => 'Luzern',                     'canton' => 'LU', 'slug' => 'luzern'],
            ['name' => 'Uri',                        'canton' => 'UR', 'slug' => 'uri'],
            ['name' => 'Schwyz',                     'canton' => 'SZ', 'slug' => 'schwyz'],
            ['name' => 'Obwalden',                   'canton' => 'OW', 'slug' => 'obwalden'],
            ['name' => 'Nidwalden',                  'canton' => 'NW', 'slug' => 'nidwalden'],
            ['name' => 'Glarus',                     'canton' => 'GL', 'slug' => 'glarus'],
            ['name' => 'Zug',                        'canton' => 'ZG', 'slug' => 'zug'],
            ['name' => 'Freiburg',                   'canton' => 'FR', 'slug' => 'freiburg'],
            ['name' => 'Solothurn',                  'canton' => 'SO', 'slug' => 'solothurn'],
            ['name' => 'Basel-Stadt',                'canton' => 'BS', 'slug' => 'basel-stadt'],
            ['name' => 'Basel-Landschaft',           'canton' => 'BL', 'slug' => 'basel-landschaft'],
            ['name' => 'Schaffhausen',               'canton' => 'SH', 'slug' => 'schaffhausen'],
            ['name' => 'Appenzell Ausserrhoden',     'canton' => 'AR', 'slug' => 'appenzell-ausserrhoden'],
            ['name' => 'Appenzell Innerrhoden',      'canton' => 'AI', 'slug' => 'appenzell-innerrhoden'],
            ['name' => 'St. Gallen',                 'canton' => 'SG', 'slug' => 'st-gallen'],
            ['name' => 'Graubünden',                 'canton' => 'GR', 'slug' => 'graubuenden'],
            ['name' => 'Aargau',                     'canton' => 'AG', 'slug' => 'aargau'],
            ['name' => 'Thurgau',                    'canton' => 'TG', 'slug' => 'thurgau'],
            ['name' => 'Tessin',                     'canton' => 'TI', 'slug' => 'tessin'],
            ['name' => 'Waadt',                      'canton' => 'VD', 'slug' => 'waadt'],
            ['name' => 'Wallis',                     'canton' => 'VS', 'slug' => 'wallis'],
            ['name' => 'Neuenburg',                  'canton' => 'NE', 'slug' => 'neuenburg'],
            ['name' => 'Genf',                       'canton' => 'GE', 'slug' => 'genf'],
            ['name' => 'Jura',                       'canton' => 'JU', 'slug' => 'jura'],
        ];

        foreach ($cantons as $i => $canton) {
            DB::table('cities')->updateOrInsert(
                ['slug' => $canton['slug']],
                array_merge($canton, ['sort_order' => 100 + $i])
            );
        }
    }

    public function down(): void {}
};
