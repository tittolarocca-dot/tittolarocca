<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('telegram_username', 100)->nullable()->after('whatsapp_number_encrypted');
            $table->string('address', 255)->nullable()->after('telegram_username');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['telegram_username', 'address']);
        });
    }
};
