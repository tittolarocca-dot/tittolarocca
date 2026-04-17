<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Unsere eigene Abo-Tabelle (zusätzlich zur Cashier subscriptions-Tabelle)
        Schema::create('platform_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_subscription_id')->unique();
            $table->decimal('amount_chf', 8, 2);
            $table->enum('status', ['active', 'cancelled', 'past_due', 'unpaid', 'trialing'])->default('active');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['subscriber_user_id', 'profile_id'], 'unique_subscriber_profile');
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_amount_chf', 10, 2);
            $table->decimal('commission_chf', 10, 2);   // 20%
            $table->decimal('net_amount_chf', 10, 2);   // 80%
            $table->enum('status', ['pending', 'processing', 'paid', 'failed'])->default('pending');
            $table->string('iban')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('platform_subscriptions');
    }
};
