<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_note');
            }
            if (! Schema::hasColumn('reviews', 'reply_rejection_reason')) {
                $table->text('reply_rejection_reason')->nullable()->after('reply_status');
            }
            if (! Schema::hasColumn('reviews', 'reply_submitted_at')) {
                $table->timestamp('reply_submitted_at')->nullable()->after('reply_rejection_reason');
            }
            if (! Schema::hasColumn('reviews', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('reviewed_at');
            }
            if (! Schema::hasColumn('reviews', 'reply_moderated_at')) {
                $table->timestamp('reply_moderated_at')->nullable()->after('moderated_at');
            }
        });

        // enum → plain string, damit 'rejected' auch beim reply_status erlaubt ist
        // (getrennte Statusfelder für Bewertung und Antwort).
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->string('reply_status')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            foreach ([
                'rejection_reason', 'reply_rejection_reason', 'reply_submitted_at',
                'moderated_at', 'reply_moderated_at',
            ] as $col) {
                if (Schema::hasColumn('reviews', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
