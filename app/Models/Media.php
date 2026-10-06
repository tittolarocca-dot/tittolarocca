<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'profile_id', 'type', 'storage_path', 'blur_path', 'variants', 'lqip',
        'width', 'height', 'mime_type',
        'visibility', 'status', 'context', 'public_published', 'rejection_reason', 'sort_order',
        'filesize_bytes', 'duration_seconds',
    ];

    protected $appends = ['url', 'src'];

    protected $casts = [
        'variants'         => 'array',
        'public_published' => 'boolean',
        'sort_order'       => 'integer',
        'filesize_bytes'   => 'integer',
        'duration_seconds' => 'integer',
        'width'            => 'integer',
        'height'           => 'integer',
    ];

    /** Original-/Video-Stream. Öffentliche Medien: stateless + CDN-cachebar. */
    public function getUrlAttribute(): string
    {
        return $this->visibility === 'public'
            ? route('media.pub.stream', $this->id)
            : route('media.stream', $this->id);
    }

    /**
     * Optimierte, versionierte Varianten-URLs (thumbnail/card/full) – nur Bilder.
     *
     * - Öffentliche, als statische Dateien PUBLIZIERTE Medien → direkte
     *   /storage/pubmedia/…-URLs (Webserver liefert ohne PHP, CDN-cachebar).
     * - Sonst öffentliche Medien → stateless PHP-Route /media/pub/… (Fallback).
     * - Private Medien → authed PHP-Route /media/… .
     * Es wird nie das volle Original verlinkt.
     */
    public function getSrcAttribute(): ?array
    {
        if ($this->type !== 'image') {
            return null;
        }

        $version  = $this->updated_at?->timestamp ?? 1;
        $variants = $this->variants ?? [];

        // Statische, web-erreichbare Dateien für publizierte öffentliche Medien.
        if ($this->visibility === 'public' && $this->public_published) {
            $build = function (string $size) use ($variants, $version) {
                $vp = $variants[$size] ?? null;
                return $vp
                    ? asset('storage/pubmedia/' . $this->profile_id . '/' . basename($vp)) . '?v=' . $version
                    : route('media.pub.variant', ['media' => $this->id, 'variant' => $size]) . '?v=' . $version;
            };
            return [
                'thumbnail' => $build('thumbnail'),
                'card'      => $build('card'),
                'full'      => $build('full'),
            ];
        }

        $routeName = $this->visibility === 'public' ? 'media.pub.variant' : 'media.variant';
        $build = function (string $size) use ($routeName, $version) {
            return route($routeName, ['media' => $this->id, 'variant' => $size]) . '?v=' . $version;
        };

        return [
            'thumbnail' => $build('thumbnail'),
            'card'      => $build('card'),
            'full'      => $build('full'),
        ];
    }

    public function profile()   { return $this->belongsTo(Profile::class); }
    /** Feed-Beiträge, zu denen dieses Medium gehört (für Visibility-Prüfung). */
    public function posts()     { return $this->belongsToMany(ProfilePost::class, 'profile_post_media', 'media_id', 'post_id'); }
    public function isPublic()  { return $this->visibility === 'public'; }
    public function isApproved(){ return $this->status === 'approved'; }
}
