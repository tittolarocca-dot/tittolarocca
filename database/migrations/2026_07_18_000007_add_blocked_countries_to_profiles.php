<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('profiles', 'blocked_countries')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            // ISO-3166-1 alpha-2 Codes, in denen das Inserat nicht angezeigt wird (max. 5)
            $table->json('blocked_countries')->nullable()->after('launch_gallery_free');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('profiles', 'blocked_countries')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('blocked_countries');
        });
    }
};
