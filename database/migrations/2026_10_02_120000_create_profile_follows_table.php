<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Echtes Follow-System (getrennt von „Favorit" und „Like").
 * Follow = „Ich möchte neue Beiträge dieses Profils verfolgen."
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('profile_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_user_id', 'profile_id']);
            $table->index('profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_follows');
    }
};
