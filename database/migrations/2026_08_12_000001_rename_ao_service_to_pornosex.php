<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Benennt den bestehenden Service-Tag "AO" (Slug: ao) in "Pornosex"
     * (Slug: pornosex) um. Die Zuordnungen in profile_tags bleiben erhalten,
     * da sie über tag_id referenzieren (dieselbe Zeile wird aktualisiert).
     */
    public function up(): void
    {
        $ao = DB::table('tags')->where('slug', 'ao')->first();

        if ($ao && ! DB::table('tags')->where('slug', 'pornosex')->exists()) {
            DB::table('tags')->where('id', $ao->id)->update([
                'name' => 'Pornosex',
                'slug' => 'pornosex',
            ]);
        }
    }

    public function down(): void
    {
        $tag = DB::table('tags')->where('slug', 'pornosex')->first();

        if ($tag && ! DB::table('tags')->where('slug', 'ao')->exists()) {
            DB::table('tags')->where('id', $tag->id)->update([
                'name' => 'AO',
                'slug' => 'ao',
            ]);
        }
    }
};
