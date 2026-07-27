<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chat_blocks')) {
            return;
        }

        Schema::create('chat_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();          // wer blockiert (Inserentin)
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete(); // wer blockiert wird (Kunde)
            $table->timestamps();

            $table->unique(['user_id', 'blocked_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_blocks');
    }
};
