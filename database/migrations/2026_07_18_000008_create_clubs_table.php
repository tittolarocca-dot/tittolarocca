<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clubs')) {
            return;
        }

        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('canton', 2);          // ISO-Kürzel des Kantons (ZH, BE, …)
            $table->string('city');
            $table->string('postal_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('category');           // Club, Studio, Bordell, …
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('website_url')->nullable();
            $table->json('opening_hours')->nullable();  // [{day,from,to}, …]
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('status')->default('active'); // active, pending, inactive, rejected
            $table->decimal('rating_average', 2, 1)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('website_clicks')->default(0);
            $table->unsignedInteger('profile_views')->default(0);
            $table->timestamps();

            $table->index(['status', 'canton']);
            $table->index('category');
            $table->index('is_premium');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
