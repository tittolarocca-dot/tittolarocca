<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profil-Feed (zeitlich sortierte Beiträge, getrennt von der Galerie).
 *
 * - profile_posts:      ein Beitrag (Text + optional Medien)
 * - profile_post_media: verknüpft Beiträge mit bestehenden Media-Zeilen (Wiederverwendung!)
 * - profile_post_likes: Likes auf Beiträge (getrennt von Profil-Likes)
 * - media.context:      'gallery' (Standard) vs. 'feed' – damit Feed-Fotos NICHT
 *                        in der Profil-Galerie doppelt erscheinen.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('profile_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('text')->nullable();
            $table->string('post_type', 10)->default('text');   // text, image, video, mixed
            $table->string('visibility', 10)->default('public'); // public, followers, private
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['profile_id', 'visibility', 'published_at']);
        });

        Schema::create('profile_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('profile_posts')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['post_id', 'media_id']);
        });

        Schema::create('profile_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('profile_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('context', 10)->default('gallery')->after('visibility'); // gallery, feed
            $table->index(['profile_id', 'context']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_post_likes');
        Schema::dropIfExists('profile_post_media');
        Schema::dropIfExists('profile_posts');
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['profile_id', 'context']);
            $table->dropColumn('context');
        });
    }
};
