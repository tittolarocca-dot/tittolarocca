<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->text('body_encrypted')->nullable()->change();
            $table->string('ppv_media_path')->nullable()->after('body_encrypted');
            $table->string('ppv_media_type', 20)->nullable()->after('ppv_media_path');
            $table->decimal('ppv_price_chf', 8, 2)->nullable()->after('ppv_media_type');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['ppv_media_path', 'ppv_media_type', 'ppv_price_chf']);
            $table->text('body_encrypted')->nullable(false)->change();
        });
    }
};
