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
                'url'        => route('media.stream', $m->id),
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
        $isImage    = in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'webp']);
        $type       = $isImage ? 'image' : 'video';
        $visibility = $request->input('visibility');

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

        Media::create([
            'profile_id'     => $profile->id,
            'type'           => $type,
            'storage_path'   => $path,
            'visibility'     => $visibility,
            'status'         => 'approved',
            'sort_order'     => $nextSort,
            'filesize_bytes' => $file->getSize(),
        ]);

        return back()->with('success', 'Datei hochgeladen und sofort sichtbar.');
    }

    public function destroy(Request $request, Media $media)
    {
        if ($media->profile->user_id !== $request->user()->id) {
            abort(403);
        }

        Storage::disk('local')->delete($media->storage_path);
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
