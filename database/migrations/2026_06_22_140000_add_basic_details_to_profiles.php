<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'nationality'   => fn (Blueprint $t) => $t->string('nationality', 60)->nullable(),
            'height_cm'     => fn (Blueprint $t) => $t->unsignedSmallInteger('height_cm')->nullable(),
            'eye_color'     => fn (Blueprint $t) => $t->string('eye_color', 30)->nullable(),
            'smoking'       => fn (Blueprint $t) => $t->boolean('smoking')->nullable(),
            'tattoo'        => fn (Blueprint $t) => $t->boolean('tattoo')->nullable(),
            'intimate_area' => fn (Blueprint $t) => $t->string('intimate_area', 30)->nullable(),
            'body_type'     => fn (Blueprint $t) => $t->string('body_type', 30)->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn('profiles', $name)) {
                continue;
            }
            Schema::table('profiles', function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    public function down(): void
    {
        foreach (['nationality', 'height_cm', 'eye_color', 'smoking', 'tattoo', 'intimate_area', 'body_type'] as $name) {
            if (Schema::hasColumn('profiles', $name)) {
                Schema::table('profiles', function (Blueprint $table) use ($name) {
                    $table->dropColumn($name);
                });
            }
        }
    }
};
