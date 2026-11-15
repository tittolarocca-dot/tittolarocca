<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\User;
use App\Services\AdminBulkImportService;
use App\Support\ImportRowValidator;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

/**
 * Admin-Bulk-Import: CSV hochladen → Vorschau/Validierung → User+Profile anlegen.
 * NUR für Administratoren (canAccess). Erstellung läuft über AdminBulkImportService.
 */
class ImportListings extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Inserate importieren';
    protected static ?string $title = 'Inserate importieren';
    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 20;
    protected static string $view = 'filament.pages.import-listings';

    /** @var mixed Hochgeladene CSV (Livewire TemporaryUploadedFile) */
    public $csv = null;

    public array $rows = [];
    public array $summary = ['ready' => 0, 'duplicate' => 0, 'error' => 0];
    public bool $analyzed = false;
    public ?array $result = null;
    public ?int $batchId = null;

    /** Zugriff ausschließlich für Administratoren (Navigation + Route). */
    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->isAdmin());
    }

    public function updatedCsv(): void
    {
        $this->analyze();
    }

    public function analyze(): void
    {
        $this->reset(['rows', 'result', 'batchId']);
        $this->summary  = ['ready' => 0, 'duplicate' => 0, 'error' => 0];
        $this->analyzed = false;

        $this->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        [$header, $records] = $this->readCsv($this->csv->getRealPath());
        if (! $header) {
            $this->addError('csv', 'CSV konnte nicht gelesen werden oder hat keine Kopfzeile.');
            return;
        }

        // Nachschlagedaten (einmalig)
        $cities = [];
        foreach (City::all(['id', 'name', 'slug']) as $c) {
            $cities[mb_strtolower($c->name)] = $c->id;
            if ($c->slug) {
                $cities[mb_strtolower($c->slug)] = $c->id;
            }
        }
        $categories = [];
        foreach (Category::all(['id', 'name', 'slug']) as $c) {
            $categories[mb_strtolower($c->name)] = $c->id;
            if ($c->slug) {
                $categories[mb_strtolower($c->slug)] = $c->id;
            }
        }
        $existingEmails = [];
        foreach (User::pluck('email') as $e) {
            $existingEmails[mb_strtolower((string) $e)] = true;
        }
        $existingNames = [];
        foreach (Profile::pluck('display_name') as $n) {
            if ($n !== null && $n !== '') {
                $existingNames[mb_strtolower((string) $n)] = true;
            }
        }

        $seenEmails = [];
        $seenNames  = [];
        $rows = [];
        foreach ($records as $rec) {
            $assoc = $this->mapRow($header, $rec);
            $res = ImportRowValidator::validate($assoc, [
                'cities'         => $cities,
                'categories'     => $categories,
                'existingEmails' => $existingEmails,
                'existingNames'  => $existingNames,
                'seenEmails'     => $seenEmails,
                'seenNames'      => $seenNames,
            ]);

            $em = $res['display']['email'];
            if ($em !== '') {
                $seenEmails[$em] = true;
            }
            $nm = mb_strtolower($res['display']['name']);
            if ($nm !== '') {
                $seenNames[$nm] = true;
            }

            $rows[] = $res;
            $this->summary[$res['status']] = ($this->summary[$res['status']] ?? 0) + 1;
        }

        $this->rows     = $rows;
        $this->analyzed = true;
    }

    public function import(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        if (! $this->analyzed) {
            return;
        }

        $ready = array_values(array_map(
            fn ($r) => $r['data'],
            array_filter($this->rows, fn ($r) => $r['status'] === 'ready')
        ));

        if (empty($ready)) {
            $this->addError('csv', 'Keine importierbaren (bereiten) Zeilen vorhanden.');
            return;
        }

        $batch = app(AdminBulkImportService::class)->import(
            $ready,
            auth()->user(),
            is_object($this->csv) ? $this->csv->getClientOriginalName() : null
        );

        $this->result = [
            'created' => $batch->created_count,
            'skipped' => $batch->skipped_count,
            'errors'  => $batch->error_count,
            'details' => $batch->getAttribute('details') ?? [],
        ];
        $this->batchId = $batch->id;

        Notification::make()
            ->title("Import abgeschlossen: {$batch->created_count} erstellt")
            ->success()
            ->send();
    }

    /** Liest Kopfzeile (mit Delimiter-Erkennung + BOM) und Datensätze (fgetcsv → quotes-sicher). */
    private function readCsv(string $path): array
    {
        $h = @fopen($path, 'r');
        if ($h === false) {
            return [null, []];
        }
        $firstLine = fgets($h);
        if ($firstLine === false) {
            fclose($h);
            return [null, []];
        }
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine); // BOM
        $delim = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        $header = array_map(fn ($c) => mb_strtolower(trim((string) $c)), str_getcsv($firstLine, $delim));

        $rows = [];
        while (($rec = fgetcsv($h, 0, $delim)) !== false) {
            if ($rec === [null] || (count($rec) === 1 && trim((string) $rec[0]) === '')) {
                continue; // Leerzeile
            }
            $rows[] = $rec;
        }
        fclose($h);

        return [$header, $rows];
    }

    private function mapRow(array $header, array $rec): array
    {
        $assoc = [];
        foreach ($header as $i => $key) {
            $assoc[$key] = $rec[$i] ?? '';
        }
        return $assoc;
    }
}
