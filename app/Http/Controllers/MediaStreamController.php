<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStreamController extends Controller
{
    public function show(Request $request, Media $media): StreamedResponse
    {
        $user    = $request->user();
        $profile = $media->profile;
        $isOwner = $user && $profile->user_id === $user->id;

        if (!$isOwner) {
            // Non-approved media only visible to owner
            if ($media->status !== 'approved') {
                abort(404);
            }
            // Private media requires active subscription
            if ($media->visibility === 'private') {
                if (!$user || !$user->isSubscribedTo($profile)) {
                    abort(403, 'Abonnement erforderlich.');
                }
            }
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($media->storage_path)) {
            abort(404);
        }

        $mimeType = match(strtolower(pathinfo($media->storage_path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            'mp4'         => 'video/mp4',
            'mov'         => 'video/quicktime',
            default       => 'application/octet-stream',
        };

        return response()->stream(function () use ($disk, $media) {
            $stream = $disk->readStream($media->storage_path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control'       => 'private, max-age=3600',
            'X-Accel-Buffering'   => 'no',
        ]);
    }
}
