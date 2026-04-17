<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body_encrypted'); // AES-256 verschlüsselt
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['to_user_id', 'read_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
