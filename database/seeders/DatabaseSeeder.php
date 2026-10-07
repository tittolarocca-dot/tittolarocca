<?php
namespace Database\Seeders;

use App\Models\City;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ListingPackage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Schweizer Städte
        $cities = [
            ['name' => 'Zürich',      'canton' => 'ZH', 'slug' => 'zuerich'],
            ['name' => 'Bern',        'canton' => 'BE', 'slug' => 'bern'],
            ['name' => 'Basel',       'canton' => 'BS', 'slug' => 'basel'],
            ['name' => 'Genf',        'canton' => 'GE', 'slug' => 'genf'],
            ['name' => 'Lausanne',    'canton' => 'VD', 'slug' => 'lausanne'],
            ['name' => 'Winterthur', 'canton' => 'ZH', 'slug' => 'winterthur'],
            ['name' => 'Luzern',      'canton' => 'LU', 'slug' => 'luzern'],
            ['name' => 'St. Gallen', 'canton' => 'SG', 'slug' => 'st-gallen'],
            ['name' => 'Lugano',      'canton' => 'TI', 'slug' => 'lugano'],
            ['name' => 'Biel',        'canton' => 'BE', 'slug' => 'biel'],
            ['name' => 'Thun',        'canton' => 'BE', 'slug' => 'thun'],
            ['name' => 'Chur',        'canton' => 'GR', 'slug' => 'chur'],
        ];
        foreach ($cities as $i => $city) {
            City::firstOrCreate(['slug' => $city['slug']], array_merge($city, ['sort_order' => $i]));
        }

        // Einzigartige, redaktionelle Stadttexte (SEO: kein Duplicate Content).
        // Nur das intro_text-Feld wird gesetzt – is_active/sort_order bleiben unberührt.
        $cityIntros = [
            'zuerich'    => 'Zürich ist die grösste Stadt der Schweiz – und auf booklola.ch findest du hier Escort-, Begleit- und Erotikinserate aus der Stadt Zürich und der ganzen Region, von Oerlikon bis an den Zürichsee. Alle Profile mit Fotos, filterbar nach Kategorie, und du nimmst diskret und direkt Kontakt auf.',
            'bern'       => 'In der Bundesstadt Bern und im Berner Umland findest du auf booklola.ch aktuelle Escort- und Begleitinserate mit Fotos. Ob in der Innenstadt oder in der Agglomeration – filtere nach Kategorie und kontaktiere die Anbieter:innen direkt und diskret.',
            'basel'      => 'Basel am Dreiländereck ist ein Hotspot für Erotik und Begleitung. Auf booklola.ch findest du Inserate aus Basel-Stadt und der Region – mit Fotos, nach Kategorie sortierbar und mit direktem, diskretem Kontakt.',
            'genf'       => 'Genf, die internationale Stadt am Lac Léman, hat eine lebendige Escort- und Begleitszene. Auf booklola.ch findest du aktuelle Inserate aus Genf und Umgebung mit Fotos – diskret filterbar und direkt kontaktierbar.',
            'lausanne'   => 'In Lausanne am Genfersee findest du auf booklola.ch Escort-, Begleit- und Erotikinserate aus der Stadt und der Waadtländer Region. Alle Profile mit Fotos, nach Kategorie filterbar und mit direktem, diskretem Kontakt.',
            'winterthur' => 'Winterthur im Kanton Zürich liegt nur einen Katzensprung von der Limmatstadt entfernt. Auf booklola.ch findest du hier Escort- und Begleitinserate aus Winterthur und Umgebung – mit Fotos, filterbar und diskret kontaktierbar.',
            'luzern'     => 'Luzern am Vierwaldstättersee ist das Tor zur Zentralschweiz. Auf booklola.ch findest du Escort-, Begleit- und Erotikinserate aus der Stadt Luzern und der Region – alle mit Fotos, nach Kategorie filterbar und mit direktem, diskretem Kontakt.',
            'st-gallen'  => 'In St. Gallen in der Ostschweiz findest du auf booklola.ch aktuelle Escort- und Begleitinserate aus der Stadt und der Region Bodensee. Mit Fotos, nach Kategorie sortierbar und diskret direkt kontaktierbar.',
            'lugano'     => 'Lugano im sonnigen Tessin verbindet mediterranes Flair mit einer lebendigen Begleitszene. Auf booklola.ch findest du Inserate aus Lugano und der Region – mit Fotos, filterbar und mit direktem, diskretem Kontakt.',
            'biel'       => 'Biel/Bienne, die zweisprachige Stadt am Bielersee, findest du auf booklola.ch mit aktuellen Escort- und Begleitinseraten aus der Stadt und dem Seeland. Alle Profile mit Fotos, nach Kategorie filterbar und diskret kontaktierbar.',
            'thun'       => 'Thun am Tor zum Berner Oberland bietet auf booklola.ch Escort-, Begleit- und Erotikinserate aus der Stadt und der Region Thunersee. Mit Fotos, filterbar nach Kategorie und mit direktem, diskretem Kontakt.',
            'chur'       => 'Chur, die älteste Stadt der Schweiz und Hauptort Graubündens, findest du auf booklola.ch mit aktuellen Escort- und Begleitinseraten aus der Stadt und der Bündner Region. Alle Profile mit Fotos, filterbar und diskret kontaktierbar.',
        ];
        foreach ($cityIntros as $slug => $intro) {
            City::where('slug', $slug)->update(['intro_text' => $intro]);
        }

        // Kategorien
        $categories = [
            ['name' => 'Escort',                 'slug' => 'escort'],
            ['name' => 'Massage',                'slug' => 'massage'],
            ['name' => 'Trans/TS',               'slug' => 'trans-ts'],
            ['name' => 'Männer',                 'slug' => 'maenner'],
            ['name' => 'Paare',                  'slug' => 'paare'],
            ['name' => 'Domina',                 'slug' => 'domina'],
            ['name' => 'Ich bin besuchbar',      'slug' => 'ich-bin-besuchbar'],
            ['name' => 'Ich komme zu dir',       'slug' => 'ich-komme-zu-dir'],
            ['name' => 'Begleitservice',         'slug' => 'begleitservice'],
            ['name' => 'Content (Fotos/Videos)', 'slug' => 'content-fotos-videos'],
        ];
        foreach ($categories as $i => $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['sort_order' => $i]));
        }

        // Tags / Leistungen
        $tagGroups = [
            null => ['GFE', 'Dinner Date', 'Übernachtung', 'Reisebegleitung',
                     'Tantra', 'Body2Body', 'Erotik', 'BDSM', 'Outdoor',
                     'Pornosex', 'Französisch', 'Anal', 'Safe Sex'],
            'Klassisch' => [
                'Sex Klassisch', 'Blasen mit Gummi',
            ],
            'Spezial' => [
                'Anal mit Schutz', 'Algierfranzösisch', 'Blasen ohne Gummi', 'Hoden Französisch',
                'Brustbesamung', 'Busenerotik', 'Deep Throat', 'Dildospiele', 'Dreier MFF',
                'Double penetration', 'Dreier MMF', 'Gesichtsfick', 'Foto/Video Aufnahmen',
                'Französisch bei Ihr', 'Gesichtsbesamung (COF)', 'Girlfriendsex', 'Gruppensex',
                'High Heels', 'Lesbensex', 'Mundvollendung (CIM)', 'Schlucken', 'Rollenspiele',
                'Sex mit Paaren', 'Strip', 'Zungenküsse', 'Sexualbegleitung',
                'Service für Behinderte', 'Squirting', 'Spitting',
            ],
            'BDSM / Fetisch' => [
                'Ballbusting', 'Bondage', 'Brustwarzentortur', 'Strapon', 'Domina', 'Einlauf',
                'Facesitting', 'Fetisch', 'Fingering Anal (aktiv)', 'Fisting Anal (passiv)',
                'Fisting Vaginal', 'Fuss Erotik', 'Gummi-Puppe', 'Kaviar aktiv', 'Kaviar passiv',
                'Keuschhaltung', 'Leicht Devot', 'Nadelung', 'Natursekt geben (aktiv)',
                'Natursekt nehmen (passiv)', 'Rimming aktiv', 'Rimming passiv', 'Sklavenerziehung',
                'Sklavin', 'Spanking', 'Trampling', 'Wachsbehandlung',
            ],
            'Massage' => [
                'Prostata-Massage', 'Entspannungs Massage', 'Erotische Massage',
                'Klassische Massage', 'Sakura-Massage', 'Thai-Massage',
            ],
        ];
        foreach ($tagGroups as $group => $tags) {
            foreach ($tags as $tag) {
                $slug = \Illuminate\Support\Str::slug($tag) ?: \Illuminate\Support\Str::slug($group . '-' . $tag);
                if (Tag::where('slug', $slug)->exists()) {
                    continue;
                }
                Tag::create(['name' => $tag, 'slug' => $slug, 'group' => $group]);
            }
        }

        // Listing-Pakete
        if (ListingPackage::count() === 0) {
        ListingPackage::insert([
            ['name' => 'Gratis Test', 'duration_days' => 30, 'price_chf' => 0.00,  'features' => json_encode(['3 öffentliche Fotos', '30 Tage Laufzeit', 'Inserat gratis verlängerbar']), 'sort_order' => 0, 'is_active' => 1],
            ['name' => 'Starter',    'duration_days' => 7,  'price_chf' => 19.00, 'features' => json_encode(['3 öffentliche Fotos', '7 Tage Laufzeit']),                            'sort_order' => 1, 'is_active' => 1],
            ['name' => 'Standard',   'duration_days' => 30, 'price_chf' => 49.00, 'features' => json_encode(['5 öffentliche Fotos', '30 Tage Laufzeit']),                           'sort_order' => 2, 'is_active' => 1],
            ['name' => 'Pro',        'duration_days' => 30, 'price_chf' => 79.00, 'features' => json_encode(['10 Fotos', 'VIP-Badge', '30 Tage Laufzeit']),                         'sort_order' => 3, 'is_active' => 1],
            ['name' => 'Premium',    'duration_days' => 90, 'price_chf' => 149.00,'features' => json_encode(['Unbegrenzte Fotos', 'Top-Platzierung', '90 Tage']),                  'sort_order' => 4, 'is_active' => 1],
        ]);
        }

        // Admin-User
        User::firstOrCreate(
            ['email' => 'admin@platform.local'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('secret123'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
