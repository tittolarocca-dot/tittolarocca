<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Kapselt die bestehende Booklola-Medienpipeline (zuvor inline im
 * Inserent\MediaController) an EINER Stelle – damit sowohl der Inserenten-Upload
 * als auch der Admin-Upload (Bulk-Import/Profilverwaltung) EXAKT dieselbe
 * Validierung, WebP-Erzeugung, Wasserzeichen, Blur, Original-Cap und
 * CDN-Publizierung nutzen. KEINE zweite Pipeline.
 */
class ProfileMediaService
{
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov'];
    private const MAX_KB       = 51200;        // 50 MB (wie bisher)
    private const MAX_PIXELS   = 40_000_000;   // 40 MP Schutz vor Dekompressionsbomben

    /**
     * Speichert EINE hochgeladene Datei für ein Profil über die bestehende
     * Pipeline und gibt den erzeugten Media-Datensatz zurück.
     *
     * @throws \RuntimeException bei ungültiger Datei / erreichtem Limit (mit Benutzer-Meldung)
     */
    public function storeUpload(Profile $profile, UploadedFile $file, string $visibility = 'public'): Media
    {
        $mime    = (string) $file->getMimeType();
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/');
        $ext     = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if ((! $isImage && ! $isVideo) || ! in_array($ext, self::ALLOWED_EXT, true)) {
            throw new \RuntimeException('Nicht unterstütztes Dateiformat (erlaubt: JPG, PNG, WebP, MP4, MOV).');
        }
        if (($file->getSize() ?? 0) > self::MAX_KB * 1024) {
            throw new \RuntimeException('Datei zu gross (max. 50 MB).');
        }
        if (! in_array($visibility, ['public', 'private'], true)) {
            $visibility = 'public';
        }

        $type = $isImage ? 'image' : 'video';

        if ($isImage) {
            $dim = @getimagesize($file->getRealPath());
            if ($dim === false) {
                throw new \RuntimeException('Ungültige oder beschädigte Bilddatei.');
            }
            if (($dim[0] * $dim[1]) > self::MAX_PIXELS) {
                throw new \RuntimeException('Bild zu gross (max. 40 Megapixel).');
            }
        }

        $limit    = $visibility === 'public' ? 10 : 30;
        $existing = $profile->media()->where('visibility', $visibility)->count();
        if ($existing >= $limit) {
            throw new \RuntimeException("Maximale Anzahl ({$limit}) für {$visibility} Medien erreicht.");
        }

        $filename = Str::uuid() . '.' . $ext;
        $path     = "media/{$profile->id}/{$filename}";
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        $nextSort = ($profile->media()->where('visibility', $visibility)->max('sort_order') ?? -1) + 1;

        $media = Media::create([
            'profile_id'     => $profile->id,
            'type'           => $type,
            'storage_path'   => $path,
            'visibility'     => $visibility,
            'status'         => 'approved',
            'sort_order'     => $nextSort,
            'filesize_bytes' => $file->getSize(),
        ]);

        // Optimierte WebP-Varianten (+ Wasserzeichen), Blur, Original-Cap, CDN-Publizierung.
        if ($type === 'image') {
            $variants = app(ImageVariants::class);
            $variants->generate($media);
            app(ImageBlur::class)->generate($media);
            $variants->capOriginal($media);
            if ($visibility === 'public') {
                $variants->publishPublic($media->refresh());
            }
        }

        return $media;
    }

    /** Löscht ein Medium inkl. aller Varianten/Blur/statischen Kopien (wie bisher). */
    public function deleteMedia(Media $media): void
    {
        $disk = Storage::disk('local');
        $disk->delete($media->storage_path);
        if ($media->blur_path) {
            $disk->delete($media->blur_path);
        }
        foreach (app(ImageVariants::class)->paths($media) as $variantPath) {
            $disk->delete($variantPath);
        }
        app(ImageVariants::class)->unpublishPublic($media);
        $media->delete();
    }

    /** Persistiert die Reihenfolge anhand einer Liste von Media-IDs (Index = sort_order). */
    public function reorderByIds(Profile $profile, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $index => $mediaId) {
            Media::where('id', $mediaId)
                ->where('profile_id', $profile->id)
                ->update(['sort_order' => $index]);
        }
    }

    /** Macht ein Medium zum Hauptbild (sort_order 0), ohne die restliche Reihenfolge zu zerstören. */
    public function setMain(Profile $profile, Media $media): void
    {
        if ((int) $media->profile_id !== (int) $profile->id) {
            return;
        }
        $ids = $profile->media()
            ->where('visibility', $media->visibility)
            ->orderBy('sort_order')->pluck('id')->all();

        $ids = array_values(array_filter($ids, fn ($id) => (int) $id !== (int) $media->id));
        array_unshift($ids, $media->id);

        $this->reorderByIds($profile, $ids);
    }
}
