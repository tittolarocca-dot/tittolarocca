<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageVariants;
use Illuminate\Console\Command;

class GenerateMediaVariants extends Command
{
    protected $signature = 'media:generate-variants {--force : Bereits vorhandene Varianten neu erzeugen}';

    protected $description = 'Erzeugt optimierte WebP-Varianten (thumbnail/card/full) für alle Bild-Medien';

    public function handle(ImageVariants $service): int
    {
        $force = (bool) $this->option('force');

        $query = Media::where('type', 'image');
        if (! $force) {
            // ohne --force nur Bilder ohne (vollständige) Varianten
            $query->where(function ($q) {
                $q->whereNull('variants')->orWhere('variants', '')->orWhere('variants', '[]');
            });
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('Keine Bilder zu verarbeiten.');
            return self::SUCCESS;
        }

        $this->info("Erzeuge Varianten für {$total} Bild(er)…");
        $bar = $this->output->createProgressBar($total);
        $ok = 0; $fail = 0;

        $query->orderBy('id')->chunkById(50, function ($chunk) use ($service, $force, $bar, &$ok, &$fail) {
            foreach ($chunk as $media) {
                try {
                    $service->generate($media, $force) !== null ? $ok++ : $fail++;
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
        $this->info("Fertig. Erzeugt/aktualisiert: {$ok}, Fehlgeschlagen: {$fail}.");

        return self::SUCCESS;
    }
}
