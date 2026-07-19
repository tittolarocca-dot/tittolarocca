<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('profiles', 'launch_gallery_free')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            // Inserentin gibt private Galerie im Launch kostenlos für registrierte Mitglieder frei.
            $table->boolean('launch_gallery_free')->default(false)->after('subscription_price_chf');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('profiles', 'launch_gallery_free')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('launch_gallery_free');
        });
    }
};
