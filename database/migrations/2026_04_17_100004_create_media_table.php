<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('storage_path'); // privater Pfad in S3/R2
            $table->string('thumbnail_path')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('filesize_bytes')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // für Videos
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
