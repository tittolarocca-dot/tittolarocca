<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('club_reports')) {
            return;
        }

        Schema::create('club_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('type');            // report = Änderung melden, claim = Eintrag beanspruchen
            $table->text('message');
            $table->string('email')->nullable();
            $table->string('status')->default('open'); // open | handled
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_reports');
    }
};
