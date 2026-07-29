<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = [
            [
                'name' => 'Velvet Lounge Zürich', 'canton' => 'ZH', 'city' => 'Zürich',
                'postal_code' => '8005', 'address' => 'Langstrasse 120', 'category' => 'Club',
                'latitude' => 47.3806, 'longitude' => 8.5285, 'phone' => '+41 44 000 00 01',
                'website_url' => 'https://example.com/velvet', 'is_premium' => true, 'is_verified' => true,
                'rating_average' => 4.6, 'rating_count' => 42,
                'description' => 'Stilvoller Erotikclub im Herzen von Zürich mit Bar und Lounge-Bereich.',
                'opening_hours' => [
                    ['day' => 'wed', 'from' => '20:00', 'to' => '04:00'],
                    ['day' => 'thu', 'from' => '20:00', 'to' => '04:00'],
                    ['day' => 'fri', 'from' => '20:00', 'to' => '05:00'],
                    ['day' => 'sat', 'from' => '20:00', 'to' => '05:00'],
                ],
            ],
            [
                'name' => 'Studio Aurora', 'canton' => 'BE', 'city' => 'Bern',
                'postal_code' => '3011', 'address' => 'Aarbergergasse 40', 'category' => 'Studio',
                'latitude' => 46.9480, 'longitude' => 7.4474, 'phone' => '+41 31 000 00 02',
                'website_url' => 'https://example.com/aurora', 'is_verified' => true,
                'rating_average' => 4.2, 'rating_count' => 18,
                'description' => 'Diskretes Studio mit mehreren Zimmern in zentraler Lage.',
                'opening_hours' => [
                    ['day' => 'mon', 'from' => '11:00', 'to' => '23:00'],
                    ['day' => 'tue', 'from' => '11:00', 'to' => '23:00'],
                    ['day' => 'wed', 'from' => '11:00', 'to' => '23:00'],
                    ['day' => 'thu', 'from' => '11:00', 'to' => '23:00'],
                    ['day' => 'fri', 'from' => '11:00', 'to' => '23:00'],
                ],
            ],
            [
                'name' => 'Saunaclub Paradiso', 'canton' => 'AG', 'city' => 'Aarau',
                'postal_code' => '5000', 'address' => 'Industriestrasse 5', 'category' => 'Saunaclub',
                'latitude' => 47.3925, 'longitude' => 8.0442, 'phone' => '+41 62 000 00 03',
                'website_url' => 'https://example.com/paradiso', 'is_premium' => true,
                'rating_average' => 4.8, 'rating_count' => 65,
                'description' => 'Grosser Saunaclub mit Wellnessbereich, Pool und Bar.',
                'opening_hours' => [
                    ['day' => 'mon', 'from' => '00:00', 'to' => '00:00'],
                    ['day' => 'tue', 'from' => '00:00', 'to' => '00:00'],
                    ['day' => 'wed', 'from' => '00:00', 'to' => '00:00'],
                    ['day' => 'thu', 'from' => '00:00', 'to' => '00:00'],
                    ['day' => 'fri', 'from' => '00:00', 'to' => '00:00'],
                    ['day' => 'sat', 'from' => '00:00', 'to' => '00:00'],
                    ['day' => 'sun', 'from' => '00:00', 'to' => '00:00'],
                ],
            ],
            [
                'name' => 'Kontaktbar Rouge', 'canton' => 'BS', 'city' => 'Basel',
                'postal_code' => '4053', 'address' => 'Gundeldingerstrasse 200', 'category' => 'Kontaktbar',
                'latitude' => 47.5379, 'longitude' => 7.5890, 'phone' => '+41 61 000 00 04',
                'website_url' => 'https://example.com/rouge',
                'rating_average' => 3.9, 'rating_count' => 11,
                'description' => 'Gemütliche Kontaktbar mit lockerer Atmosphäre.',
                'opening_hours' => [
                    ['day' => 'thu', 'from' => '18:00', 'to' => '02:00'],
                    ['day' => 'fri', 'from' => '18:00', 'to' => '03:00'],
                    ['day' => 'sat', 'from' => '18:00', 'to' => '03:00'],
                ],
            ],
            [
                'name' => 'Massage Lotus', 'canton' => 'LU', 'city' => 'Luzern',
                'postal_code' => '6003', 'address' => 'Pilatusstrasse 15', 'category' => 'Massage',
                'phone' => '+41 41 000 00 05', 'website_url' => 'https://example.com/lotus',
                'is_verified' => true, 'rating_average' => 4.4, 'rating_count' => 27,
                'description' => 'Entspannende Massagen in ruhigem Ambiente.',
                'opening_hours' => [
                    ['day' => 'mon', 'from' => '10:00', 'to' => '20:00'],
                    ['day' => 'tue', 'from' => '10:00', 'to' => '20:00'],
                    ['day' => 'wed', 'from' => '10:00', 'to' => '20:00'],
                    ['day' => 'thu', 'from' => '10:00', 'to' => '20:00'],
                    ['day' => 'fri', 'from' => '10:00', 'to' => '20:00'],
                    ['day' => 'sat', 'from' => '10:00', 'to' => '18:00'],
                ],
            ],
            [
                'name' => 'Club Diamant', 'canton' => 'TI', 'city' => 'Lugano',
                'postal_code' => '6900', 'address' => 'Via Motta 8', 'category' => 'Club',
                'latitude' => 46.0037, 'longitude' => 8.9511, 'website_url' => 'https://example.com/diamant',
                'rating_average' => 4.1, 'rating_count' => 9,
                'description' => 'Eleganter Nachtclub im Tessin.',
                'opening_hours' => [
                    ['day' => 'fri', 'from' => '21:00', 'to' => '04:00'],
                    ['day' => 'sat', 'from' => '21:00', 'to' => '04:00'],
                ],
            ],
        ];

        foreach ($clubs as $data) {
            Club::firstOrCreate(
                ['name' => $data['name'], 'city' => $data['city']],
                array_merge(['status' => 'active'], $data),
            );
        }
    }
}
