<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Models\Profile;
use App\Services\ProfileMediaService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

/**
 * Admin-Foto-Zuordnung über die bestehende Medienpipeline (ProfileMediaService).
 * Zwei Modi:
 *   ?batch={id}   → alle Profile dieses Bulk-Imports (von oben nach unten)
 *   ?profile={id} → ein einzelnes Profil (Wiederverwendung in der Profilverwaltung)
 *
 * NUR für Administratoren. Nicht in der Navigation (per Button/Action erreichbar).
 */
class ManagePhotos extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $title = 'Fotos zu Profilen hinzufügen';
    protected string $view = 'filament.pages.manage-photos';

    /** @var array<int, array> hochzuladende Dateien je Profil-ID */
    public array $uploads = [];

    public ?int $batch = null;
    public ?int $profileId = null;

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->isAdmin());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; // nur über Button/Action, nicht als Menüpunkt
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->batch     = request()->integer('batch') ?: null;
        $this->profileId = request()->integer('profile') ?: null;
    }

    /** Profile im aktuellen Modus (Batch oder Einzelprofil). */
    public function profiles()
    {
        $q = Profile::query()->with(['city', 'user']);

        if ($this->profileId) {
            $q->where('id', $this->profileId);
        } elseif ($this->batch) {
            $q->whereIn('user_id', \App\Models\User::where('import_batch_id', $this->batch)->select('id'));
        } else {
            return collect();
        }

        return $q->orderBy('display_name')->get();
    }

    /** Öffentliche Bilder eines Profils für den Thumbnail-Strip. */
    public function photosFor(int $profileId): array
    {
        return Media::where('profile_id', $profileId)
            ->where('visibility', 'public')
            ->orderBy('sort_order')
            ->get()
            ->values()
            ->map(fn (Media $m, int $i) => [
                'id'   => $m->id,
                'url'  => $m->src['thumbnail'] ?? $m->url,
                'main' => $i === 0,
            ])->all();
    }

    /** Auto-Verarbeitung, sobald Dateien für ein Profil hochgeladen wurden. */
    public function updatedUploads($value, $key): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $profile = $this->ownedProfile((int) $key);
        if (! $profile) {
            return;
        }

        $files = is_array($value) ? $value : [$value];
        $ok = 0; $fail = 0;
        $svc = app(ProfileMediaService::class);

        foreach ($files as $file) {
            if (! $file) {
                continue;
            }
            try {
                $svc->storeUpload($profile, $file, 'public');
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
            }
        }

        $this->uploads[$key] = []; // Eingabe zurücksetzen

        Notification::make()
            ->title("{$profile->display_name}: {$ok} hochgeladen" . ($fail ? ", {$fail} fehlgeschlagen" : ''))
            ->{$fail ? 'warning' : 'success'}()
            ->send();
    }

    public function deletePhoto(int $mediaId): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $media = Media::find($mediaId);
        if ($media && $this->ownedProfile((int) $media->profile_id)) {
            app(ProfileMediaService::class)->deleteMedia($media);
        }
    }

    public function setMain(int $mediaId): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $media = Media::find($mediaId);
        $profile = $media ? $this->ownedProfile((int) $media->profile_id) : null;
        if ($media && $profile) {
            app(ProfileMediaService::class)->setMain($profile, $media);
        }
    }

    /** Reihenfolge nach Drag&Drop (Liste von Media-IDs) speichern. */
    public function reorder(int $profileId, array $ids): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $profile = $this->ownedProfile($profileId);
        if ($profile) {
            app(ProfileMediaService::class)->reorderByIds($profile, array_map('intval', $ids));
        }
    }

    /** Ein Bild um eine Position verschieben ($dir = -1 hoch, +1 runter). */
    public function move(int $mediaId, int $dir): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $media   = Media::find($mediaId);
        $profile = $media ? $this->ownedProfile((int) $media->profile_id) : null;
        if (! $media || ! $profile) {
            return;
        }

        $ids = $profile->media()->where('visibility', $media->visibility)
            ->orderBy('sort_order')->pluck('id')->map('intval')->all();
        $pos = array_search((int) $media->id, $ids, true);
        if ($pos === false) {
            return;
        }
        $new = $pos + $dir;
        if ($new < 0 || $new >= count($ids)) {
            return;
        }
        [$ids[$pos], $ids[$new]] = [$ids[$new], $ids[$pos]];
        app(ProfileMediaService::class)->reorderByIds($profile, $ids);
    }

    /** Stellt sicher, dass das Profil zum aktuellen Modus (Batch/Einzelprofil) gehört. */
    private function ownedProfile(int $profileId): ?Profile
    {
        return $this->profiles()->firstWhere('id', $profileId);
    }
}
