<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $newTags = ['Ich bin besuchbar', 'Ich komme zu dir', 'Begleitservice'];

        foreach ($newTags as $name) {
            $slug = Str::slug($name);
            if (!DB::table('tags')->where('slug', $slug)->exists()) {
                DB::table('tags')->insert(['name' => $name, 'slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        DB::table('tags')->whereIn('slug', [
            'ich-bin-besuchbar',
            'ich-komme-zu-dir',
            'begleitservice',
        ])->delete();
    }
};
