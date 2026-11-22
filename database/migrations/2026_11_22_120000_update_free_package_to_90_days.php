<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Launch-Modus: Gratis-Paket von 30 auf 90 Tage Laufzeit anheben
     * (Anzeige + Aktivierungs-Laufzeit laufen über duration_days des Pakets).
     */
    public function up(): void
    {
        DB::table('listing_packages')
            ->where('name', 'Gratis Test')
            ->update([
                'duration_days' => 90,
                'features'      => json_encode([
                    '3 öffentliche Fotos',
                    '90 Tage Laufzeit',
                    'Inserat gratis verlängerbar',
                ]),
            ]);
    }

    public function down(): void
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
};
