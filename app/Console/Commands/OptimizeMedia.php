<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageBlur;
use App\Services\ImageVariants;
use Illuminate\Console\Command;

/**
 * Erzeugt für bestehende Bild-Medien die optimierten Varianten (thumbnail/card/full)
 * und kappt das Original auf max. 2000px. Läuft auf der CLI (genug Speicher/Zeit),
 * NICHT im Web-Request. Idempotent – bereits erzeugte Varianten werden übersprungen.
 *
 *   php artisan media:optimize
 *   php artisan media:optimize --force   (Varianten neu erzeugen)
 */
class OptimizeMedia extends Command
{
    protected $signature = 'media:optimize {--force : Vorhandene Varianten neu erzeugen}';
    protected $description = 'Varianten erzeugen und Originale auf 2000px kappen (Bestandsbilder)';

    public function handle(ImageVariants $variants, ImageBlur $blur): int
    {
        $force = (bool) $this->option('force');
        $query = Media::where('type', 'image');
        $total = $query->count();

        $this->info("Optimiere {$total} Bild-Medien" . ($force ? ' (force)' : '') . ' …');
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok = 0; $fail = 0;
        $query->chunkById(50, function ($chunk) use ($variants, $blur, $force, &$ok, &$fail, $bar) {
            foreach ($chunk as $media) {
                try {
                    $variants->generate($media, $force);
                    if (! $media->blur_path) {
                        $blur->generate($media->refresh());
                    }
                    $variants->capOriginal($media->refresh());
                    $ok++;
                } catch (\Throwable $e) {
                    $fail++;
                    $this->newLine();
                    $this->warn("Media {$media->id}: " . $e->getMessage());
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Fertig. Erfolgreich: {$ok}, Fehler: {$fail}.");

        return self::SUCCESS;
    }
}
