<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('age');
            $table->string('sexuality')->nullable()->after('gender');
            $table->string('body_type')->nullable()->after('sexuality');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('body_type');
            $table->unsignedTinyInteger('weight_kg')->nullable()->after('height_cm');
            $table->string('smoker')->nullable()->after('weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['gender', 'sexuality', 'body_type', 'height_cm', 'weight_kg', 'smoker']);
        });
    }
};
