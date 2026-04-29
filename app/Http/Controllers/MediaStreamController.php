<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Message;
use App\Models\PpvPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaStreamController extends Controller
{
    public function show(Request $request, Media $media)
    {
        $user    = $request->user();
        $profile = $media->profile;
        $isOwner = $user && $profile->user_id === $user->id;

        if (!$isOwner) {
            if ($media->status !== 'approved') {
                abort(404);
            }
            if ($media->visibility === 'private') {
                if (!$user || !$user->isSubscribedTo($profile)) {
                    abort(403, 'Abonnement erforderlich.');
                }
            }
        }

        return $this->streamFile($request, $media->storage_path);
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

        // Regular (non-PPV) chat media → both parties can view
        if (!$message->isPpv()) {
            return $this->streamFile($request, $message->ppv_media_path);
        }

        // PPV media → sender (creator) always; receiver needs paid purchase
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
