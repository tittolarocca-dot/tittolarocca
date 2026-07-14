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

        $file       = $request->file('file');
        $isImage    = str_starts_with((string) $file->getMimeType(), 'image/');
        $type       = $isImage ? 'image' : 'video';
        $visibility = $request->input('visibility');

        // Bilddateien serverseitig echt prüfen (nicht nur an der Endung) und
        // extrem grosse Abmessungen abweisen (Schutz vor Dekompressions-Bomben).
        if ($isImage) {
            $dim = @getimagesize($file->getRealPath());
            if ($dim === false) {
                return back()->with('error', 'Ungültige oder beschädigte Bilddatei.');
            }
            if (($dim[0] * $dim[1]) > 40_000_000) {
                return back()->with('error', 'Bild zu gross (max. 40 Megapixel).');
            }
        }

        // Limit check
        $existingCount = $profile->media()->where('visibility', $visibility)->count();
        $limit = $visibility === 'public' ? 10 : 30;
        if ($existingCount >= $limit) {
            return back()->with('error', "Maximale Anzahl ({$limit}) für {$visibility} Medien erreicht.");
        }

        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;
        $path      = "media/{$profile->id}/{$filename}";

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

        // Optimierte WebP-Varianten + Locked-Content-Blur erzeugen (synchron)
        if ($type === 'image') {
            app(\App\Services\ImageVariants::class)->generate($media);
            app(\App\Services\ImageBlur::class)->generate($media);
        }

        return back()->with('success', 'Datei hochgeladen und sofort sichtbar.');
    }

    public function destroy(Request $request, Media $media)
    {
        if ($media->profile->user_id !== $request->user()->id) {
            abort(403);
        }

        $disk = Storage::disk('local');
        $disk->delete($media->storage_path);
        if ($media->blur_path) {
            $disk->delete($media->blur_path);
        }
        foreach (app(\App\Services\ImageVariants::class)->paths($media) as $variantPath) {
            $disk->delete($variantPath);
        }
        $media->delete();

        return back()->with('success', 'Datei gelöscht.');
    }

    public function reorder(Request $request)
    {
        $profile = $request->user()->profile;
        if (!$profile) abort(403);

        $request->validate(['order' => ['required', 'array']]);

        foreach ($request->input('order') as $index => $mediaId) {
            Media::where('id', $mediaId)
                ->where('profile_id', $profile->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['ok' => true]);
    }
}
