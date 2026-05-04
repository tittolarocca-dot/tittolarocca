<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->enum('verification_status', ['unverified', 'pending', 'approved', 'rejected'])
                ->default('unverified')
                ->after('status');
            $table->string('verification_photo')->nullable()->after('verification_status');
            $table->text('verification_rejected_reason')->nullable()->after('verification_photo');
            $table->timestamp('verification_submitted_at')->nullable()->after('verification_rejected_reason');
            $table->timestamp('verification_reviewed_at')->nullable()->after('verification_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_photo',
                'verification_rejected_reason',
                'verification_submitted_at',
                'verification_reviewed_at',
            ]);
        });
    }
};
