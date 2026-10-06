<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Markiert, ob die öffentlichen Varianten eines Mediums zusätzlich als statische
 * Dateien in der public-Disk liegen (web-erreichbar unter /storage/pubmedia/…).
 * Dann werden sie direkt vom Webserver ausgeliefert (kein PHP) und sind
 * automatisch CDN-cachebar. Ist das Flag false, greift der PHP-Fallback
 * (/media/pub/…), sodass nie ein Bild bricht.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('public_published')->default(false)->after('context');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('public_published');
        });
    }
};
