<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aktivitätsstatus (Online / zuletzt aktiv).
 * - users.last_active_at: wird gedrosselt (max. alle 5 Min.) aktualisiert.
 * - profiles.show_activity_status: Inserentin kann den Status verbergen.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->after('remember_token');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('show_activity_status')->default(true)->after('launch_gallery_free');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_active_at');
        });
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('show_activity_status');
        });
    }
};
