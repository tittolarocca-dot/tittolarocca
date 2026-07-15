<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar_status')) {
                $table->string('avatar_status')->nullable()->after('avatar_path');
            }
            if (! Schema::hasColumn('users', 'avatar_rejection_reason')) {
                $table->text('avatar_rejection_reason')->nullable()->after('avatar_status');
            }
            if (! Schema::hasColumn('users', 'avatar_moderated_at')) {
                $table->timestamp('avatar_moderated_at')->nullable()->after('avatar_rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['avatar_status', 'avatar_rejection_reason', 'avatar_moderated_at'] as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
