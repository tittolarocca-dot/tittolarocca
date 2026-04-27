<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fan_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->string('type')->default('subscription'); // subscription | push
            $table->decimal('gross_amount_chf', 10, 2);
            $table->decimal('platform_fee_chf', 10, 2);
            $table->decimal('creator_net_chf', 10, 2);
            $table->string('currency', 3)->default('CHF');
            $table->string('status')->default('paid'); // paid | refunded | failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
