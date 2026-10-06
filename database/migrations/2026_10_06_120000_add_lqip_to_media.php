<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LQIP (Low-Quality Image Placeholder): winziges, unscharfes Vorschaubild als
 * data-URI (base64). Wird sofort angezeigt, während das echte Bild lädt –
 * keine leeren dunklen Boxen mehr (wie die base64-Platzhalter des Wettbewerbs).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->text('lqip')->nullable()->after('variants');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('lqip');
        });
    }
};
