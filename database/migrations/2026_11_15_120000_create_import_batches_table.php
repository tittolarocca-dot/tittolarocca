<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gruppiert einen Admin-Bulk-Import (CSV) und verknüpft die dabei NEU erstellten
 * User mit dem Batch. Grundlage für: „nur Profile dieses Imports" (Foto-Seite),
 * Audit und einen späteren sicheren Rollback.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->timestamps();
        });

        if (! Schema::hasColumn('users', 'import_batch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('import_batch_id')->nullable()->after('id')
                    ->constrained('import_batches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'import_batch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('import_batch_id');
            });
        }
        Schema::dropIfExists('import_batches');
    }
};
