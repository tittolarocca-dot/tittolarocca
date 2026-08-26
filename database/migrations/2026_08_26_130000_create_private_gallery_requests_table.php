<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zugangs-Anfragen für die private Galerie (Launch-Phase).
 * Ein Mitglied fragt eine Inserentin an; die Inserentin gibt pro Person frei.
 * Im Vollbetrieb läuft der Zugang stattdessen über Abo/Bezahlung.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('private_gallery_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // anfragendes Mitglied
            $table->string('status', 10)->default('pending'); // pending, approved, declined
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'user_id']);
            $table->index(['profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_gallery_requests');
    }
};
