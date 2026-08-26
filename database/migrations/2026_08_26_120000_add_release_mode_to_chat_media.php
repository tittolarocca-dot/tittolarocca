<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Chat-Medien bekommen einen Freigabe-Modus:
 *   free   – sofort für das Mitglied sichtbar
 *   paid   – gesperrt, Freischaltung über Online-Zahlung (Stripe)
 *   manual – gesperrt, Inserentin schaltet manuell frei (z. B. nach TWINT)
 *
 * PPV-Käufe erhalten zusätzlich eine `method` (stripe | manual), damit eine
 * manuelle Freigabe von einer echten Online-Zahlung unterscheidbar bleibt.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('ppv_media_mode', 10)->nullable()->after('ppv_price_chf');
        });

        Schema::table('ppv_purchases', function (Blueprint $table) {
            $table->string('method', 20)->nullable()->after('status');
        });

        // Bestehende Datensätze rückwirkend einordnen.
        DB::table('messages')->whereNotNull('ppv_price_chf')->update(['ppv_media_mode' => 'paid']);
        DB::table('messages')->whereNull('ppv_price_chf')->whereNotNull('ppv_media_path')->update(['ppv_media_mode' => 'free']);
        DB::table('ppv_purchases')->whereNull('method')->update(['method' => 'stripe']);
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('ppv_media_mode');
        });
        Schema::table('ppv_purchases', function (Blueprint $table) {
            $table->dropColumn('method');
        });
    }
};
