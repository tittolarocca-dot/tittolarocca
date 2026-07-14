<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'variants')) {
                $table->json('variants')->nullable()->after('blur_path');
            }
            if (! Schema::hasColumn('media', 'width')) {
                $table->unsignedInteger('width')->nullable()->after('variants');
            }
            if (! Schema::hasColumn('media', 'height')) {
                $table->unsignedInteger('height')->nullable()->after('width');
            }
            if (! Schema::hasColumn('media', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->after('height');
            }
        });

        // Totes Feld entfernen – wurde nie befüllt oder verwendet.
        if (Schema::hasColumn('media', 'thumbnail_path')) {
            Schema::table('media', function (Blueprint $table) {
                $table->dropColumn('thumbnail_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable();
            }
            foreach (['variants', 'width', 'height', 'mime_type'] as $col) {
                if (Schema::hasColumn('media', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
