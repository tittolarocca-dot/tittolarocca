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
            City::create(array_merge($city, ['sort_order' => $i]));
        }

        // Kategorien
        $categories = [
            ['name' => 'Escort',   'slug' => 'escort'],
            ['name' => 'Massage',  'slug' => 'massage'],
            ['name' => 'Trans/TS', 'slug' => 'trans-ts'],
            ['name' => 'Männer',   'slug' => 'maenner'],
            ['name' => 'Paare',    'slug' => 'paare'],
            ['name' => 'Domina',   'slug' => 'domina'],
        ];
        foreach ($categories as $i => $cat) {
            Category::create(array_merge($cat, ['sort_order' => $i]));
        }

        // Tags / Leistungen
        $tags = ['GFE', 'Dinner Date', 'Übernachtung', 'Reisebegleitung',
                 'Tantra', 'Body2Body', 'Erotik', 'BDSM', 'Outdoor',
                 'AO', 'Französisch', 'Anal', 'Safe Sex'];
        foreach ($tags as $tag) {
            Tag::create(['name' => $tag, 'slug' => \Illuminate\Support\Str::slug($tag)]);
        }

        // Listing-Pakete
        ListingPackage::insert([
            ['name' => 'Gratis Test', 'duration_days' => 14, 'price_chf' => 0.00,  'features' => json_encode(['3 öffentliche Fotos', '14 Tage Laufzeit', 'Testmodus – kein Stripe']), 'sort_order' => 0, 'is_active' => 1],
            ['name' => 'Starter',    'duration_days' => 7,  'price_chf' => 19.00, 'features' => json_encode(['3 öffentliche Fotos', '7 Tage Laufzeit']),                            'sort_order' => 1, 'is_active' => 1],
            ['name' => 'Standard',   'duration_days' => 30, 'price_chf' => 49.00, 'features' => json_encode(['5 öffentliche Fotos', '30 Tage Laufzeit']),                           'sort_order' => 2, 'is_active' => 1],
            ['name' => 'Pro',        'duration_days' => 30, 'price_chf' => 79.00, 'features' => json_encode(['10 Fotos', 'VIP-Badge', '30 Tage Laufzeit']),                         'sort_order' => 3, 'is_active' => 1],
            ['name' => 'Premium',    'duration_days' => 90, 'price_chf' => 149.00,'features' => json_encode(['Unbegrenzte Fotos', 'Top-Platzierung', '90 Tage']),                  'sort_order' => 4, 'is_active' => 1],
        ]);

        // Admin-User
        User::create([
            'name'              => 'Admin',
            'email'             => 'admin@platform.local',
            'password'          => Hash::make('secret123'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
