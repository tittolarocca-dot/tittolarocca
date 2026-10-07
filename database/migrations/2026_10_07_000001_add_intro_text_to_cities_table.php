<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            // Einzigartiger, redaktioneller Stadttext für die lokale Landingpage
            // (SEO: vermeidet Duplicate Content über die Städte-Vorlage hinweg).
            $table->text('intro_text')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('intro_text');
        });
    }
};
