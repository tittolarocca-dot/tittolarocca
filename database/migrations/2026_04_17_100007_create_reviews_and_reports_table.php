<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('stars'); // 1–5
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('admin_note')->nullable();
            $table->text('inserent_reply')->nullable();
            $table->enum('reply_status', ['pending', 'approved'])->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['reviewer_user_id', 'profile_id'], 'one_review_per_profile');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_type'); // profile, media, message, review
            $table->unsignedBigInteger('target_id');
            $table->string('reason');
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->text('note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('reviews');
    }
};
