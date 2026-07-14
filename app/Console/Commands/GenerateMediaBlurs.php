<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageBlur;
use Illuminate\Console\Command;

class GenerateMediaBlurs extends Command
{
    protected $signature = 'media:blur {--force : Auch bereits vorhandene Vorschauen neu erzeugen}';

    protected $description = 'Erzeugt serverseitige Locked-Content-Vorschauen (Blur) für alle Bild-Medien';

    public function handle(ImageBlur $blur): int
    {
        $query = Media::where('type', 'image');
        if (! $this->option('force')) {
            $query->whereNull('blur_path');
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('Keine Bilder zu verarbeiten.');
            return self::SUCCESS;
        }

        $this->info("Erzeuge Vorschauen für {$total} Bild(er)…");
        $bar = $this->output->createProgressBar($total);
        $ok = 0; $fail = 0;

        $query->orderBy('id')->chunkById(100, function ($chunk) use ($blur, $bar, &$ok, &$fail) {
            foreach ($chunk as $media) {
                $blur->generate($media) ? $ok++ : $fail++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Fertig. Erzeugt: {$ok}, Fehlgeschlagen: {$fail}.");

        return self::SUCCESS;
    }
}
