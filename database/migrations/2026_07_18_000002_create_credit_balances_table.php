<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_balances')) {
            return;
        }

        Schema::create('credit_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(0);          // aktueller Saldo
            $table->integer('total_granted')->default(0);    // alle Gutschriften (inkl. Bonus)
            $table->integer('total_spent')->default(0);      // alle Abbuchungen
            $table->integer('total_purchased')->default(0);  // später: gekaufte Credits
            $table->integer('total_bonus')->default(0);      // Bonus-Credits (Launch/Galerie)
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_balances');
    }
};
