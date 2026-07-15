<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Erzeugt aus einem Upload ein quadratisches, optimiertes Profilfoto (WebP)
 * im PRIVATEN Storage. GD re-encodiert vollständig → EXIF/GPS werden entfernt.
 */
class UserAvatar
{
    private const SIZE       = 512;         // Kantenlänge (quadratisch)
    private const QUALITY    = 82;
    private const MAX_PIXELS = 40_000_000;  // Schutz vor Dekompressions-Bomben

    /**
     * Verarbeitet den Upload, speichert ihn und setzt avatar_path am User.
     * Gibt true bei Erfolg, false bei ungültigem Bild.
     */
    public function store(User $user, UploadedFile $file): bool
    {
        $info = @getimagesize($file->getRealPath());
        if ($info === false) {
            return false;
        }
        [$ow, $oh] = $info;
        if ($ow < 1 || $oh < 1 || ($ow * $oh) > self::MAX_PIXELS) {
            return false;
        }

        $src = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if ($src === false) {
            return false;
        }

        // Quadratischer Center-Crop
        $side = min($ow, $oh);
        $sx   = (int) (($ow - $side) / 2);
        $sy   = (int) (($oh - $side) / 2);

        $dst = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, self::SIZE, self::SIZE, $side, $side);
        imagedestroy($src);

        ob_start();
        $ok = function_exists('imagewebp')
            ? imagewebp($dst, null, self::QUALITY)
            : imagejpeg($dst, null, self::QUALITY);
        $data = ob_get_clean();
        imagedestroy($dst);

        if (! $ok || ! $data) {
            return false;
        }

        $ext  = function_exists('imagewebp') ? 'webp' : 'jpg';
        $path = "avatars/{$user->id}.{$ext}";

        // alte Datei (evtl. andere Endung) entfernen
        $this->delete($user);

        Storage::disk('local')->put($path, $data);

        // Neues Foto muss erst vom Admin freigegeben werden
        $user->forceFill([
            'avatar_path'             => $path,
            'avatar_status'           => 'pending',
            'avatar_rejection_reason' => null,
            'avatar_moderated_at'     => null,
        ])->save();

        return true;
    }

    public function delete(User $user): void
    {
        if ($user->avatar_path && Storage::disk('local')->exists($user->avatar_path)) {
            Storage::disk('local')->delete($user->avatar_path);
        }
        if ($user->avatar_path || $user->avatar_status) {
            $user->forceFill([
                'avatar_path'             => null,
                'avatar_status'           => null,
                'avatar_rejection_reason' => null,
                'avatar_moderated_at'     => null,
            ])->save();
        }
    }
}
