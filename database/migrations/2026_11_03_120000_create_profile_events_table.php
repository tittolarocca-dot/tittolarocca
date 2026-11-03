<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zeitgestempelte Einzel-Events pro Profil für die Inserenten-Statistik.
 *
 * Nötig, weil total_views (Zähler) und profile_visits (eindeutige Besucher,
 * upsert) KEINE Zeitreihe liefern. Hier wird jedes Ereignis mit Zeitstempel
 * gespeichert, damit „Diese Woche vs. Vorwoche" etc. möglich ist.
 *
 * type: view | whatsapp | call | telegram | website
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->foreignId('visitor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();

            // Schnelle Bereichsabfragen pro Profil + Typ.
            $table->index(['profile_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_events');
    }
};
