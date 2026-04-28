<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ppv_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_chf', 8, 2);
            $table->string('status', 20)->default('pending'); // pending, paid
            $table->string('stripe_session_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['message_id', 'buyer_user_id']);
            $table->index(['buyer_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppv_purchases');
    }
};
