<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return redirect()->route('inserat.profile.edit')
                ->with('error', 'Bitte erstelle zuerst dein Profil.');
        }

        $media = $profile->media()
            ->orderBy('visibility')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'type'       => $m->type,
                'visibility' => $m->visibility,
                'status'     => $m->status,
                'rejection_reason' => $m->rejection_reason,
                'sort_order' => $m->sort_order,
                'url'        => $m->type === 'image'
                    ? ($m->src['card'] ?? route('media.stream', $m->id))
                    : route('media.stream', $m->id),
                'created_at' => $m->created_at->format('d.m.Y'),
            ]);

        return Inertia::render('Inserent/Media/Index', [
            'media'   => $media,
            'profile' => [
                'id'     => $profile->id,
                'slug'   => $profile->slug,
                'status' => $profile->status,
            ],
            'limits' => [
                'public_max'  => 10,
                'private_max' => 30,
                'public_used' => $media->where('visibility', 'public')->count(),
                'private_used'=> $media->where('visibility', 'private')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        $request->validate([
            'file'       => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:51200'],
            'visibility' => ['required', 'in:public,private'],
        ]);

        // Zentrale Medienpipeline (WebP/Wasserzeichen/Blur/Cap/Publizierung).
        try {
            app(\App\Services\ProfileMediaService::class)->storeUpload(
                $profile,
                $request->file('file'),
                $request->input('visibility')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Datei hochgeladen und sofort sichtbar.');
    }

    public function destroy(Request $request, Media $media)
    {
        if ($media->profile->user_id !== $request->user()->id) {
            abort(403);
        }

        app(\App\Services\ProfileMediaService::class)->deleteMedia($media);

        return back()->with('success', 'Datei gelöscht.');
    }

    public function reorder(Request $request)
    {
        $profile = $request->user()->profile;
        if (!$profile) abort(403);

        $request->validate(['order' => ['required', 'array']]);

        app(\App\Services\ProfileMediaService::class)->reorderByIds($profile, $request->input('order'));

        return response()->json(['ok' => true]);
    }
}
