<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Erzeugt optimierte WebP-Varianten (thumbnail/card/full) im PRIVATEN Storage.
 *
 * - Seitenverhältnis bleibt erhalten, es wird nie vergrössert.
 * - GD re-encodiert vollständig → EXIF/GPS/Metadaten werden entfernt.
 * - Varianten privater Medien liegen im selben privaten Storage wie das
 *   Original und werden über dieselbe Autorisierung ausgeliefert.
 */
class ImageVariants
{
    /** Zielbreiten in px (max, kein Upscaling). */
    public const SIZES = [
        'thumbnail' => 320,
        'card'      => 720,
        'full'      => 1600,
    ];

    private const QUALITY   = 80;
    private const MAX_PIXELS = 40_000_000; // Schutz vor Dekompressions-Bomben (~40 MP)

    /**
     * Erzeugt fehlende (oder mit $force alle) Varianten und speichert die
     * Pfade + Originaldimensionen am Media. Gibt die Variantenliste zurück
     * oder null bei Fehler.
     */
    public function generate(Media $media, bool $force = false): ?array
    {
        if ($media->type !== 'image') {
            return null;
        }

        $disk = Storage::disk('local');
        if (! $media->storage_path || ! $disk->exists($media->storage_path)) {
            return null;
        }

        $raw  = $disk->get($media->storage_path);
        $info = @getimagesizefromstring($raw);
        if ($info === false) {
            Log::warning("ImageVariants: Media {$media->id} ist kein gültiges Bild.");
            return null;
        }

        [$ow, $oh] = $info;
        $mime = $info['mime'] ?? null;

        if ($ow < 1 || $oh < 1 || ($ow * $oh) > self::MAX_PIXELS) {
            Log::warning("ImageVariants: Media {$media->id} überschreitet die Pixelgrenze ({$ow}x{$oh}).");
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return null;
        }

        $useWebp = function_exists('imagewebp');
        $ext     = $useWebp ? 'webp' : 'jpg';
        $variants = $media->variants ?? [];

        try {
            foreach (self::SIZES as $name => $maxW) {
                if (! $force && ! empty($variants[$name]) && $disk->exists($variants[$name])) {
                    continue;
                }

                // Zielmasse – niemals grösser als das Original
                $tw = min($ow, $maxW);
                $th = max(1, (int) round($oh * $tw / $ow));

                $dst = imagecreatetruecolor($tw, $th);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $ow, $oh);

                ob_start();
                $ok = $useWebp
                    ? imagewebp($dst, null, self::QUALITY)
                    : imagejpeg($dst, null, self::QUALITY);
                $data = ob_get_clean();
                imagedestroy($dst);

                if ($ok && $data) {
                    $path = "media/{$media->profile_id}/variants/{$media->id}_{$name}.{$ext}";
                    $disk->put($path, $data);
                    $variants[$name] = $path;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("ImageVariants: Fehler bei Media {$media->id}: " . $e->getMessage());
            imagedestroy($src);
            return null;
        }

        $lqip = $this->makeLqip($src, $ow, $oh);

        imagedestroy($src);

        $media->forceFill([
            'variants'       => $variants,
            'lqip'           => $lqip,
            'width'          => $ow,
            'height'         => $oh,
            'mime_type'      => $mime,
            'filesize_bytes' => $media->filesize_bytes ?: strlen($raw),
        ])->save();

        return $variants;
    }

    /** Winziges, unscharfes Vorschaubild als data-URI (base64) für LQIP-Platzhalter. */
    private function makeLqip($src, int $ow, int $oh): ?string
    {
        $w = 24;
        $h = max(1, (int) round($oh * $w / $ow));

        $small = imagecreatetruecolor($w, $h);
        imagealphablending($small, false);
        imagesavealpha($small, true);
        imagecopyresampled($small, $src, 0, 0, 0, 0, $w, $h, $ow, $oh);
        for ($i = 0; $i < 2; $i++) {
            @imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $useWebp = function_exists('imagewebp');
        ob_start();
        $ok = $useWebp ? imagewebp($small, null, 45) : imagejpeg($small, null, 40);
        $data = ob_get_clean();
        imagedestroy($small);

        if (! $ok || ! $data) {
            return null;
        }

        return 'data:' . ($useWebp ? 'image/webp' : 'image/jpeg') . ';base64,' . base64_encode($data);
    }

    /** Alle Variant-Storage-Pfade eines Media (für das Löschen). */
    public function paths(Media $media): array
    {
        return array_values($media->variants ?? []);
    }

    /**
     * Verkleinert das gespeicherte ORIGINAL in-place auf max. $maxW px Breite
     * (gleiches Format). Damit landet nie ein Multi-MB-/4000px-Original im
     * Storage – die Varianten (≤1600px) genügen für die Auslieferung, das
     * Original dient nur noch als Generierungs-/Notfall-Quelle.
     * Sollte NACH generate() laufen (Varianten aus bester Quelle).
     */
    public function capOriginal(Media $media, int $maxW = 2000): void
    {
        if ($media->type !== 'image') {
            return;
        }

        $disk = Storage::disk('local');
        if (! $media->storage_path || ! $disk->exists($media->storage_path)) {
            return;
        }

        $raw  = $disk->get($media->storage_path);
        $info = @getimagesizefromstring($raw);
        if ($info === false) {
            return;
        }

        [$ow, $oh] = $info;
        if ($ow < 1 || $oh < 1 || ($ow * $oh) > self::MAX_PIXELS || $ow <= $maxW) {
            return; // schon klein genug
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return;
        }

        $tw  = $maxW;
        $th  = max(1, (int) round($oh * $tw / $ow));
        $dst = imagecreatetruecolor($tw, $th);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $ow, $oh);

        $ext = strtolower(pathinfo($media->storage_path, PATHINFO_EXTENSION));
        ob_start();
        $ok = match ($ext) {
            'png'  => imagepng($dst, null, 6),
            'webp' => function_exists('imagewebp') ? imagewebp($dst, null, self::QUALITY) : imagejpeg($dst, null, self::QUALITY),
            default => imagejpeg($dst, null, self::QUALITY),
        };
        $data = ob_get_clean();
        imagedestroy($dst);
        imagedestroy($src);

        if ($ok && $data) {
            $disk->put($media->storage_path, $data);
            $media->forceFill([
                'width'          => $tw,
                'height'         => $th,
                'filesize_bytes' => strlen($data),
            ])->save();
        }
    }
}
