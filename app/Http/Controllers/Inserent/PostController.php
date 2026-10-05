<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\ProfilePost;
use App\Services\ImageBlur;
use App\Services\ImageVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    /** Verwaltungsseite: eigene Feed-Beiträge auflisten + Composer. */
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return redirect()->route('inserat.profile.edit')
                ->with('error', 'Bitte erstelle zuerst dein Profil.');
        }

        $posts = $profile->posts()
            ->with('media')
            ->withCount('likedBy')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => $this->mapPost($p));

        return Inertia::render('Inserent/Posts/Index', [
            'posts'   => $posts,
            'profile' => [
                'display_name' => $profile->display_name,
                'avatar_url'   => $profile->publicMedia()->first()?->src['thumbnail'] ?? null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        $request->validate([
            'text'       => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:public,followers,private'],
            'media'      => ['nullable', 'array', 'max:10'],
            'media.*'    => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:51200'],
        ]);

        $files = $request->file('media', []);

        if (trim((string) $request->input('text')) === '' && count($files) === 0) {
            return back()->with('error', 'Ein Beitrag braucht Text oder mindestens ein Medium.');
        }

        $visibility = $request->input('visibility');

        $post = ProfilePost::create([
            'profile_id'     => $profile->id,
            'author_user_id' => $request->user()->id,
            'text'           => $request->input('text') ?: null,
            'post_type'      => 'text',
            'visibility'     => $visibility,
            'published_at'   => now(),
        ]);

        $hasImage = false;
        $hasVideo = false;
        foreach (array_values($files) as $i => $file) {
            $media = $this->storeFeedMedia($profile->id, $file, $visibility);
            if (! $media) {
                continue;
            }
            $post->media()->attach($media->id, ['sort_order' => $i]);
            $media->type === 'video' ? $hasVideo = true : $hasImage = true;
        }

        $post->update(['post_type' => $this->postType($hasImage, $hasVideo)]);

        return back()->with('success', 'Beitrag veröffentlicht.');
    }

    public function update(Request $request, ProfilePost $post)
    {
        $this->authorizeOwner($request, $post);

        $request->validate([
            'text'       => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:public,followers,private'],
        ]);

        if (trim((string) $request->input('text')) === '' && $post->media()->count() === 0) {
            return back()->with('error', 'Ein Beitrag braucht Text oder mindestens ein Medium.');
        }

        $post->update([
            'text'       => $request->input('text') ?: null,
            'visibility' => $request->input('visibility'),
        ]);

        // Medien-Sichtbarkeit nachziehen (öffentlich vs. geschützt) und updated_at
        // bumpen, damit die ?v=-URL wechselt (keine veraltet-öffentlichen CDN-Treffer).
        $mediaVisibility = $request->input('visibility') === 'public' ? 'public' : 'private';
        Media::whereIn('id', $post->media()->pluck('media.id'))->update([
            'visibility' => $mediaVisibility,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Beitrag aktualisiert.');
    }

    public function destroy(Request $request, ProfilePost $post)
    {
        $this->authorizeOwner($request, $post);

        $disk     = Storage::disk('local');
        $variants = app(ImageVariants::class);

        foreach ($post->media as $media) {
            $disk->delete($media->storage_path);
            if ($media->blur_path) {
                $disk->delete($media->blur_path);
            }
            foreach ($variants->paths($media) as $variantPath) {
                $disk->delete($variantPath);
            }
            $media->delete(); // profile_post_media-Pivot via cascade
        }

        $post->delete();

        return back()->with('success', 'Beitrag gelöscht.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Lädt eine Datei als Feed-Medium ab (gleiche Pipeline wie die Galerie). */
    private function storeFeedMedia(int $profileId, $file, string $postVisibility): ?Media
    {
        $isImage = str_starts_with((string) $file->getMimeType(), 'image/');

        if ($isImage) {
            $dim = @getimagesize($file->getRealPath());
            if ($dim === false || ($dim[0] * $dim[1]) > 40_000_000) {
                return null;
            }
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = "media/{$profileId}/{$filename}";
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        $media = Media::create([
            'profile_id'     => $profileId,
            'type'           => $isImage ? 'image' : 'video',
            'storage_path'   => $path,
            'visibility'     => $postVisibility === 'public' ? 'public' : 'private',
            'status'         => 'approved',
            'context'        => 'feed',
            'sort_order'     => 0,
            'filesize_bytes' => $file->getSize(),
        ]);

        if ($isImage) {
            app(ImageVariants::class)->generate($media);
            app(ImageBlur::class)->generate($media);
            app(ImageVariants::class)->capOriginal($media);
        }

        return $media->fresh();
    }

    private function postType(bool $hasImage, bool $hasVideo): string
    {
        return match (true) {
            $hasImage && $hasVideo => 'mixed',
            $hasVideo              => 'video',
            $hasImage              => 'image',
            default                => 'text',
        };
    }

    private function mapPost(ProfilePost $p): array
    {
        return [
            'id'         => $p->id,
            'text'       => $p->text,
            'visibility' => $p->visibility,
            'post_type'  => $p->post_type,
            'time'       => \App\Support\RelativeTime::short($p->published_at ?? $p->created_at),
            'likes'      => $p->liked_by_count ?? $p->likedBy()->count(),
            'media'      => $p->media->map(fn ($m) => [
                'id'   => $m->id,
                'type' => $m->type,
                'src'  => $m->type === 'image' ? ($m->src['card'] ?? $m->url) : $m->url,
            ])->values(),
        ];
    }

    private function authorizeOwner(Request $request, ProfilePost $post): void
    {
        $post->loadMissing('profile');
        abort_unless($post->profile && $post->profile->user_id === $request->user()->id, 403);
    }
}
