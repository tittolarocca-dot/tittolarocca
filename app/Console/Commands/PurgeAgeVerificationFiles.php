<?php

namespace App\Console\Commands;

use App\Models\AdminLog;
use App\Models\AgeVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sichere Löschung der sensiblen 18+ Prüfdateien (Ausweis + Video) NACH der
 * konfigurierten Aufbewahrungsfrist. Der Verifizierungs-Status (verified) sowie
 * die unkritischen Metadaten bleiben erhalten – nur die Rohdateien verschwinden.
 *
 * Steuerung über config/features.php → age_verification_retention_days
 * (ENV: AGE_VERIFICATION_RETENTION_DAYS). Ist nichts gesetzt, wird purge_after
 * bei der Freigabe gar nicht gefüllt und dieser Command löscht nichts.
 *
 * Idealerweise täglich per Cron/Scheduler aufrufen:
 *   php artisan age-verifications:purge
 */
class PurgeAgeVerificationFiles extends Command
{
    protected $signature = 'age-verifications:purge {--dry-run : Nur anzeigen, nichts löschen}';
    protected $description = 'Löscht abgelaufene 18+ Ausweis-/Videodateien sicher (Status bleibt erhalten).';

    public function handle(): int
    {
        $due = AgeVerification::query()
            ->whereNotNull('purge_after')
            ->where('purge_after', '<=', now())
            ->whereNull('files_purged_at')
            ->where(fn ($q) => $q->whereNotNull('document_path')->orWhereNotNull('video_path'))
            ->get();

        if ($due->isEmpty()) {
            $this->info('Keine fälligen Dateien zum Löschen.');
            return self::SUCCESS;
        }

        $dry = (bool) $this->option('dry-run');
        $count = 0;

        foreach ($due as $av) {
            foreach (['document_path', 'video_path'] as $col) {
                $path = $av->{$col};
                if ($path && Storage::disk('local')->exists($path)) {
                    if (! $dry) {
                        Storage::disk('local')->delete($path);
                    }
                    $this->line(($dry ? '[dry] ' : '') . "gelöscht: AV #{$av->id} {$col}");
                }
            }

            if (! $dry) {
                $av->update([
                    'document_path'   => null,
                    'document_mime'   => null,
                    'video_path'      => null,
                    'video_mime'      => null,
                    'files_purged_at' => now(),
                ]);
                AdminLog::record('age_verification.files_purged', 'age_verification', $av->id);
            }
            $count++;
        }

        $this->info(($dry ? '[dry-run] ' : '') . "{$count} Verifizierung(en) verarbeitet.");
        return self::SUCCESS;
    }
}
