<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profile_owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_visited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['profile_id', 'visitor_user_id']);
            $table->index('profile_owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_visits');
    }
};
