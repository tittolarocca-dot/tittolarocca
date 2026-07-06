<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tags', 'group')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->string('group')->nullable()->after('slug');
            });
        }

        $softcore = [
            'Foto-Aufnahmen', 'Striptease', 'Kuscheln', 'Zungenküsse', 'Dirty Talk',
            'Intimrasur', 'Dusch-/Badespiele', 'Ölmassage', 'Erotische Massage',
            'Thai-Massage', 'Tantra-Sex', 'Busensex', 'Vaginal-Sex', 'Schenkelsex',
            'Girlfriendsex', 'Oralverkehr', 'Lecken', 'Masturbation', 'Fingern',
            'Dildo-/Vibratorspiele', '69', 'Handjob', 'Fuß-Erotik', 'Spanking passiv',
            'Spanking aktiv', 'Video-Aufnahmen',
        ];

        $hardcore = [
            'Gesichtsbesamung', 'Körperbesamung', 'Facesitting passiv', 'Facesitting aktiv',
            'Deepthroat', 'Squirting', 'Hardcore Foto-Aufnahmen', 'Analverkehr passiv',
            'Analverkehr aktiv', 'Anal-Fingern aktiv', 'Anal-Fingern passiv', 'Rimming aktiv',
            'Rimming passiv', 'Fisting aktiv', 'Fisting passiv', 'Sandwich', 'Dreier MMF',
            'Dreier MFF', 'Männerschuss', 'Gangbang Party', 'Lesben-Spiele', 'Homo-Spiele',
            'Hardcore Video-Aufnahmen',
        ];

        $this->seedGroup($softcore, 'Softcore Service');
        $this->seedGroup($hardcore, 'Hardcore Service');
    }

    private function seedGroup(array $names, string $group): void
    {
        foreach ($names as $name) {
            $slug = Str::slug($name) ?: Str::slug($group . '-' . $name);

            // Skip if a tag with this slug already exists (no duplicates).
            if (DB::table('tags')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('tags')->insert([
                'name'  => $name,
                'slug'  => $slug,
                'group' => $group,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tags')->whereIn('group', ['Softcore Service', 'Hardcore Service'])->delete();

        if (Schema::hasColumn('tags', 'group')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropColumn('group');
            });
        }
    }
};
