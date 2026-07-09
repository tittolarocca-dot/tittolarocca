<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('nationality', 60)->nullable()->after('age');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('nationality');
            $table->string('eye_color', 30)->nullable()->after('height_cm');
            $table->boolean('smoking')->nullable()->after('eye_color');
            $table->boolean('tattoo')->nullable()->after('smoking');
            $table->string('intimate_area', 30)->nullable()->after('tattoo');
            $table->string('body_type', 30)->nullable()->after('intimate_area');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'nationality', 'height_cm', 'eye_color',
                'smoking', 'tattoo', 'intimate_area', 'body_type',
            ]);
        });
    }
};
