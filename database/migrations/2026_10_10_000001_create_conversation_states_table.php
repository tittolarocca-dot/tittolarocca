<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_states', function (Blueprint $table) {
            $table->id();
            // Sicht EINES Nutzers auf eine Konversation mit other_user_id.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('other_user_id')->constrained('users')->cascadeOnDelete();
            // Verstecken: aus der Liste wegpacken, Verlauf bleibt (kommt bei neuer Nachricht zurück).
            $table->timestamp('hidden_at')->nullable();
            // Löschen: Verlauf für DIESEN Nutzer bis zu diesem Zeitpunkt ausblenden.
            $table->timestamp('cleared_at')->nullable();
            // Als ungelesen markiert (empfängerseitig – verfälscht NICHT die Lesebestätigung der Gegenseite).
            $table->timestamp('marked_unread_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'other_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_states');
    }
};
