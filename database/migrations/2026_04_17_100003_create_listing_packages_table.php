<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listing_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('duration_days');
            $table->decimal('price_chf', 8, 2);
            $table->json('features')->nullable(); // z.B. ["5 Fotos", "Basic-Sichtbarkeit"]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('listing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_package_id')->constrained()->restrictOnDelete();
            $table->decimal('amount_chf', 8, 2);
            $table->string('currency', 3)->default('CHF');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('listing_orders');
        Schema::dropIfExists('listing_packages');
    }
};
