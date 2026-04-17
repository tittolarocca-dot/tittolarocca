<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['member', 'inserent', 'moderator', 'admin'])->default('member')->after('email');
            $table->enum('status', ['active', 'blocked'])->default('active')->after('role');
            $table->timestamp('blocked_at')->nullable()->after('status');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'blocked_at']);
        });
    }
};
