<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Message;
use App\Models\PpvPurchase;
use App\Services\ImageBlur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaStreamController extends Controller
{
    /**
     * Liefert die serverseitig weichgezeichnete Locked-Content-Vorschau.
     * Enthält niemals das Original – gefahrlos an alle auslieferbar.
     */
    public function preview(Request $request, Media $media, ImageBlur $blur)
    {
        if ($media->type !== 'image' || $media->status !== 'approved') {
            return $this->outputPlaceholder($blur);
        }

        $disk = Storage::disk('local');

        // Blur fehlt (Altbestand)? → einmalig nacherzeugen
        if (! $media->blur_path || ! $disk->exists($media->blur_path)) {
            $blur->generate($media->refresh());
        }

        if ($media->blur_path && $disk->exists($media->blur_path)) {
            return response($disk->get($media->blur_path), 200, [
                'Content-Type'  => 'image/jpeg',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        return $this->outputPlaceholder($blur);
    }

    private function outputPlaceholder(ImageBlur $blur)
    {
        return response($blur->placeholderBytes(), 200, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function show(Request $request, Media $media)
    {
        $this->authorizeOriginal($request, $media);
        return $this->streamFile($request, $media->storage_path);
    }

    /**
     * Liefert eine optimierte Variante (thumbnail/card/full).
     * WICHTIG: identische Berechtigungsprüfung wie beim Original – scharfe
     * Varianten privater Medien nur an Owner/Abonnenten.
     */
    public function variant(Request $request, Media $media, string $variant, ImageBlur $blur)
    {
        if (! array_key_exists($variant, \App\Services\ImageVariants::SIZES)) {
            abort(404);
        }

        $this->authorizeOriginal($request, $media);

        $variants = $media->variants ?? [];
        $path     = $variants[$variant] ?? null;
        $disk     = Storage::disk('local');

        // Variante fehlt (noch nicht erzeugt) → auf Original-Stream zurückfallen
        if (! $path || ! $disk->exists($path)) {
            return $this->streamFile($request, $media->storage_path);
        }

        $isPublic  = $media->visibility === 'public';
        $mime      = str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';
        // Öffentliche Varianten: lange, unveränderliche Cache-Zeit (URL ist versioniert).
        // Private Varianten: nur privat cachen (nie in Shared-Caches/CDN).
        $cache = $isPublic
            ? 'public, max-age=31536000, immutable'
            : 'private, max-age=3600';

        return response($disk->get($path), 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => $cache,
        ]);
    }

    /** Gemeinsame Autorisierung für Original + scharfe Varianten. */
    private function authorizeOriginal(Request $request, Media $media): void
    {
        $user    = $request->user();
        $profile = $media->profile;
        $isOwner = $user && $profile->user_id === $user->id;

        if (!$isOwner) {
            if ($media->status !== 'approved') {
                abort(404);
            }
            if ($media->visibility === 'private') {
                // Launch-Zugang: gleiche Regel wie ProfileController@show –
                // eingeloggtes Mitglied, wenn die Inserentin die Galerie im Launch freigegeben hat.
                $launchAccess = config('features.launch_mode')
                    && $profile->launch_gallery_free
                    && $user !== null;

                if (!$launchAccess && (!$user || !$user->isSubscribedTo($profile))) {
                    abort(403, 'Zugriff nicht erlaubt.');
                }
            }
        }
    }

    public function ppv(Request $request, Message $message)
    {
        if (!$message->ppv_media_path) {
            abort(404);
        }

        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $isParticipant = $message->from_user_id === $user->id
                      || $message->to_user_id   === $user->id;

        if (!$isParticipant) {
            abort(403);
        }

        // Freie Chat-Medien → beide Seiten dürfen sie sehen.
        if (!$message->requiresUnlock()) {
            return $this->streamFile($request, $message->ppv_media_path);
        }

        // Gesperrte Medien → Erstellerin immer; Empfänger nur nach Freischaltung
        // (bezahlt via Stripe ODER manuell freigegeben – beides erzeugt einen
        // PpvPurchase mit status „paid").
        $isCreator = $message->from_user_id === $user->id;
        if (!$isCreator) {
            $purchased = PpvPurchase::where('message_id', $message->id)
                ->where('buyer_user_id', $user->id)
                ->where('status', 'paid')
                ->exists();

            if (!$purchased) {
                abort(403, 'Kauf erforderlich.');
            }
        }

        return $this->streamFile($request, $message->ppv_media_path);
    }

    private function streamFile(Request $request, string $path)
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            'gif'         => 'image/gif',
            'mp4'         => 'video/mp4',
            'mov'         => 'video/quicktime',
            'webm'        => 'video/webm',
            default       => 'application/octet-stream',
        };

        $size = $disk->size($path);

        if ($request->hasHeader('Range')) {
            return $this->streamRange($disk, $path, $size, $mimeType, $request->header('Range'));
        }

        return response()->stream(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 65536);
                    flush();
                }
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $size,
            'Content-Disposition' => 'inline',
            'Accept-Ranges'       => 'bytes',
            'Cache-Control'       => 'private, max-age=3600',
            'X-Accel-Buffering'   => 'no',
        ]);
    }

    private function streamRange($disk, string $path, int $size, string $mimeType, string $rangeHeader)
    {
        preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $m);
        $start = ($m[1] !== '') ? (int) $m[1] : 0;
        $end   = ($m[2] !== '') ? (int) $m[2] : $size - 1;

        if ($end >= $size) {
            $end = $size - 1;
        }
        $length = $end - $start + 1;

        return response()->stream(function () use ($disk, $path, $start, $length) {
            $stream = $disk->readStream($path);
            if ($stream) {
                fseek($stream, $start);
                $remaining = $length;
                while (!feof($stream) && $remaining > 0) {
                    $chunk     = fread($stream, min(65536, $remaining));
                    $remaining -= strlen($chunk);
                    echo $chunk;
                    flush();
                }
                fclose($stream);
            }
        }, 206, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $length,
            'Content-Range'       => "bytes {$start}-{$end}/{$size}",
            'Accept-Ranges'       => 'bytes',
            'Content-Disposition' => 'inline',
            'Cache-Control'       => 'private, max-age=3600',
            'X-Accel-Buffering'   => 'no',
        ]);
    }
}
