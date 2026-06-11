<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visited_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('visitor_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_visited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['visited_user_id', 'visitor_user_id']);
            $table->index('visited_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_visits');
    }
};
