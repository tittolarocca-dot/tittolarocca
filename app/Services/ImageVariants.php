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

        imagedestroy($src);

        $media->forceFill([
            'variants'       => $variants,
            'width'          => $ow,
            'height'         => $oh,
            'mime_type'      => $mime,
            'filesize_bytes' => $media->filesize_bytes ?: strlen($raw),
        ])->save();

        return $variants;
    }

    /** Alle Variant-Storage-Pfade eines Media (für das Löschen). */
    public function paths(Media $media): array
    {
        return array_values($media->variants ?? []);
    }
}
