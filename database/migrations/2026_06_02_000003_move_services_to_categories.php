<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Remove from tags if they were accidentally added there
        DB::table('tags')->whereIn('slug', [
            'ich-bin-besuchbar',
            'ich-komme-zu-dir',
            'begleitservice',
        ])->delete();

        // Add to categories
        $maxSort = DB::table('categories')->max('sort_order') ?? 0;

        $newCategories = ['Ich bin besuchbar', 'Ich komme zu dir', 'Begleitservice'];

        foreach ($newCategories as $i => $name) {
            $slug = Str::slug($name);
            if (!DB::table('categories')->where('slug', $slug)->exists()) {
                DB::table('categories')->insert([
                    'name'       => $name,
                    'slug'       => $slug,
                    'parent_id'  => null,
                    'is_active'  => true,
                    'sort_order' => $maxSort + $i + 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('categories')->whereIn('slug', [
            'ich-bin-besuchbar',
            'ich-komme-zu-dir',
            'begleitservice',
        ])->delete();
    }
};
