<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'gender'      => fn (Blueprint $t) => $t->string('gender', 20)->nullable(),
            'origin'      => fn (Blueprint $t) => $t->string('origin', 30)->nullable(),
            'weight_kg'   => fn (Blueprint $t) => $t->unsignedSmallInteger('weight_kg')->nullable(),
            'cup_size'    => fn (Blueprint $t) => $t->string('cup_size', 2)->nullable(),
            'breast_type' => fn (Blueprint $t) => $t->string('breast_type', 20)->nullable(),
            'has_video'   => fn (Blueprint $t) => $t->boolean('has_video')->default(false),
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
        foreach (['gender', 'origin', 'weight_kg', 'cup_size', 'breast_type', 'has_video'] as $name) {
            if (Schema::hasColumn('profiles', $name)) {
                Schema::table('profiles', function (Blueprint $table) use ($name) {
                    $table->dropColumn($name);
                });
            }
        }
    }
};
