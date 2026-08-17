<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Launch-Modus: Gratis-Paket auf 30 Tage anheben und die Feature-Texte
     * entschärfen ("Testmodus – kein Stripe" verwirrt Inserierende).
     */
    public function up(): void
    {
        DB::table('listing_packages')
            ->where('name', 'Gratis Test')
            ->update([
                'duration_days' => 30,
                'features'      => json_encode([
                    '3 öffentliche Fotos',
                    '30 Tage Laufzeit',
                    'Inserat gratis verlängerbar',
                ]),
            ]);
    }

    public function down(): void
    {
        DB::table('listing_packages')
            ->where('name', 'Gratis Test')
            ->update([
                'duration_days' => 14,
                'features'      => json_encode([
                    '3 öffentliche Fotos',
                    '14 Tage Laufzeit',
                    'Testmodus – kein Stripe',
                ]),
            ]);
    }
};
