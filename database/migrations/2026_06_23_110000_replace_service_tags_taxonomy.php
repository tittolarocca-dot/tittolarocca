<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Alte Softcore/Hardcore-Gruppen entfernen – werden durch die neue
        // Struktur (Klassisch / Spezial / BDSM-Fetisch / Massage) ersetzt.
        $oldIds = DB::table('tags')
            ->whereIn('group', ['Softcore Service', 'Hardcore Service'])
            ->pluck('id');

        if ($oldIds->isNotEmpty()) {
            DB::table('profile_tags')->whereIn('tag_id', $oldIds)->delete();
            DB::table('tags')->whereIn('id', $oldIds)->delete();
        }

        foreach ($this->groups() as $group => $names) {
            foreach ($names as $name) {
                $slug = Str::slug($name) ?: Str::slug($group . '-' . $name);
                if (DB::table('tags')->where('slug', $slug)->exists()) {
                    continue; // keine Duplikate
                }
                DB::table('tags')->insert([
                    'name'  => $name,
                    'slug'  => $slug,
                    'group' => $group,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('tags')
            ->whereIn('group', ['Klassisch', 'Spezial', 'BDSM / Fetisch', 'Massage'])
            ->delete();
    }

    private function groups(): array
    {
        return [
            'Klassisch' => [
                'Sex Klassisch', 'Blasen mit Gummi',
            ],
            'Spezial' => [
                'Anal mit Schutz', 'Algierfranzösisch', 'Blasen ohne Gummi', 'Hoden Französisch',
                'Brustbesamung', 'Busenerotik', 'Deep Throat', 'Dildospiele', 'Dreier MFF',
                'Double penetration', 'Dreier MMF', 'Gesichtsfick', 'Foto/Video Aufnahmen',
                'Französisch bei Ihr', 'Gesichtsbesamung (COF)', 'Girlfriendsex', 'Gruppensex',
                'High Heels', 'Lesbensex', 'Mundvollendung (CIM)', 'Schlucken', 'Rollenspiele',
                'Sex mit Paaren', 'Strip', 'Zungenküsse', 'Sexualbegleitung',
                'Service für Behinderte', 'Squirting', 'Spitting',
            ],
            'BDSM / Fetisch' => [
                'Ballbusting', 'Bondage', 'Brustwarzentortur', 'Strapon', 'Domina', 'Einlauf',
                'Facesitting', 'Fetisch', 'Fingering Anal (aktiv)', 'Fisting Anal (passiv)',
                'Fisting Vaginal', 'Fuss Erotik', 'Gummi-Puppe', 'Kaviar aktiv', 'Kaviar passiv',
                'Keuschhaltung', 'Leicht Devot', 'Nadelung', 'Natursekt geben (aktiv)',
                'Natursekt nehmen (passiv)', 'Rimming aktiv', 'Rimming passiv', 'Sklavenerziehung',
                'Sklavin', 'Spanking', 'Trampling', 'Wachsbehandlung',
            ],
            'Massage' => [
                'Prostata-Massage', 'Entspannungs Massage', 'Erotische Massage',
                'Klassische Massage', 'Sakura-Massage', 'Thai-Massage',
            ],
        ];
    }
};
