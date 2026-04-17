<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('whatsapp_number_encrypted')->nullable(); // AES-256
            $table->decimal('subscription_price_chf', 8, 2)->default(10.00);
            $table->enum('status', ['draft', 'active', 'expired', 'blocked'])->default('draft');
            $table->timestamp('listing_expires_at')->nullable();
            $table->timestamp('featured_until')->nullable();
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->unsignedInteger('total_subscribers')->default(0);
            $table->unsignedInteger('total_views')->default(0);
            $table->timestamps();
        });

        Schema::create('profile_tags', function (Blueprint $table) {
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['profile_id', 'tag_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('profile_tags');
        Schema::dropIfExists('profiles');
    }
};
