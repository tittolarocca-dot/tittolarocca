<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $maxSort = DB::table('categories')->max('sort_order') ?? 0;

        DB::table('categories')->insertOrIgnore([
            'name'       => 'Content (Fotos/Videos)',
            'slug'       => 'content-fotos-videos',
            'is_active'  => true,
            'sort_order' => $maxSort + 1,
        ]);
    }

    public function down(): void
    {
        DB::table('categories')->where('slug', 'content-fotos-videos')->delete();
    }
};
