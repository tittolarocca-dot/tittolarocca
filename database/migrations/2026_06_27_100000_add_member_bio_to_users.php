<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'gender'         => fn () => $table->string('gender', 20)->nullable(),
                'age'            => fn () => $table->unsignedTinyInteger('age')->nullable(),
                'height_cm'      => fn () => $table->unsignedSmallInteger('height_cm')->nullable(),
                'weight_kg'      => fn () => $table->unsignedSmallInteger('weight_kg')->nullable(),
                // Kein DB-FK: SQLite kann per ALTER TABLE keine Foreign Keys ergänzen.
                // Integrität wird per Validierung (exists:cities,id) sichergestellt.
                'city_id'        => fn () => $table->unsignedBigInteger('city_id')->nullable()->index(),
                'languages'      => fn () => $table->json('languages')->nullable(),
                'smoking'        => fn () => $table->boolean('smoking')->nullable(),
                'bio'            => fn () => $table->text('bio')->nullable(),
                'preferences'    => fn () => $table->text('preferences')->nullable(),
                'deactivated_at' => fn () => $table->timestamp('deactivated_at')->nullable(),
            ];
            foreach ($cols as $name => $make) {
                if (! Schema::hasColumn('users', $name)) {
                    $make();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['gender', 'age', 'height_cm', 'weight_kg', 'city_id', 'languages', 'smoking', 'bio', 'preferences', 'deactivated_at'] as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
