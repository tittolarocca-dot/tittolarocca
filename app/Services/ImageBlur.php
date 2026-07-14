<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Erzeugt serverseitig eine stark weichgezeichnete "Locked Content"-Vorschau.
 *
 * Technik: das Original wird zuerst extrem herunterskaliert (Detail geht
 * unwiderruflich verloren), mehrfach Gaussian-geblurrt und anschliessend
 * wieder hochskaliert. Aus der Vorschau lässt sich das Original NICHT
 * rekonstruieren – sie ist damit gefahrlos an Nicht-Abonnenten auslieferbar.
 */
class ImageBlur
{
    private const TINY_WIDTH   = 48;   // Downscale-Breite (weniger = stärker verpixelt/blur)
    private const BLUR_PASSES  = 14;   // Gaussian-Durchgänge auf dem Miniaturbild
    private const OUT_WIDTH    = 500;  // Anzeigebreite der Vorschau
    private const OUT_PASSES   = 3;    // Nachglättung nach dem Hochskalieren

    /**
     * Erzeugt (falls möglich) die Blur-Datei und speichert den Pfad am Media.
     * Gibt den Storage-Pfad zurück oder null bei Fehler.
     */
    public function generate(Media $media): ?string
    {
        if ($media->type !== 'image') {
            return null;
        }

        $disk = Storage::disk('local');
        if (! $media->storage_path || ! $disk->exists($media->storage_path)) {
            return null;
        }

        try {
            $src = @imagecreatefromstring($disk->get($media->storage_path));
            if ($src === false) {
                return null;
            }

            $data = $this->blurResource($src);
            imagedestroy($src);

            if ($data === null) {
                return null;
            }

            $blurPath = "media/{$media->profile_id}/blur/{$media->id}.jpg";
            $disk->put($blurPath, $data);
            $media->forceFill(['blur_path' => $blurPath])->save();

            return $blurPath;
        } catch (\Throwable $e) {
            Log::warning("ImageBlur: konnte Vorschau für Media {$media->id} nicht erzeugen: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Solide, dunkle Fallback-Kachel (JPEG-Bytes), falls kein Blur erzeugbar ist.
     * Enthält niemals Originaldaten.
     */
    public function placeholderBytes(): string
    {
        $img = imagecreatetruecolor(self::OUT_WIDTH, (int) round(self::OUT_WIDTH * 1.33));
        $bg  = imagecolorallocate($img, 26, 26, 26); // #1a1a1a
        imagefill($img, 0, 0, $bg);
        ob_start();
        imagejpeg($img, null, 60);
        $bytes = ob_get_clean();
        imagedestroy($img);
        return $bytes;
    }

    private function blurResource($src): ?string
    {
        $w = imagesx($src);
        $h = imagesy($src);
        if ($w < 1 || $h < 1) {
            return null;
        }

        // 1) extrem herunterskalieren
        $tw = self::TINY_WIDTH;
        $th = max(1, (int) round($h * $tw / $w));
        $small = imagecreatetruecolor($tw, $th);
        imagecopyresampled($small, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
        for ($i = 0; $i < self::BLUR_PASSES; $i++) {
            imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
        }

        // 2) wieder auf Anzeigegrösse hochskalieren + nachglätten
        $ow = self::OUT_WIDTH;
        $oh = max(1, (int) round($th * $ow / $tw));
        $out = imagecreatetruecolor($ow, $oh);
        imagecopyresampled($out, $small, 0, 0, 0, 0, $ow, $oh, $tw, $th);
        for ($i = 0; $i < self::OUT_PASSES; $i++) {
            imagefilter($out, IMG_FILTER_GAUSSIAN_BLUR);
        }
        // leicht abdunkeln für den "Locked"-Look
        imagefilter($out, IMG_FILTER_BRIGHTNESS, -25);

        ob_start();
        imagejpeg($out, null, 55);
        $bytes = ob_get_clean();

        imagedestroy($small);
        imagedestroy($out);

        return $bytes ?: null;
    }
}
