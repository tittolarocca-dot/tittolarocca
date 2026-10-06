<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'profile_id', 'type', 'storage_path', 'blur_path', 'variants',
        'width', 'height', 'mime_type',
        'visibility', 'status', 'context', 'rejection_reason', 'sort_order',
        'filesize_bytes', 'duration_seconds',
    ];

    protected $appends = ['url', 'src'];

    protected $casts = [
        'variants'         => 'array',
        'sort_order'       => 'integer',
        'filesize_bytes'   => 'integer',
        'duration_seconds' => 'integer',
        'width'            => 'integer',
        'height'           => 'integer',
    ];

    /** Original-/Video-Stream (auth-geprüft in MediaStreamController). */
    public function getUrlAttribute(): string
    {
        return route('media.stream', $this->id);
    }

    /**
     * Optimierte, versionierte Varianten-URLs (thumbnail/card/full) – nur Bilder.
     * Zeigt IMMER auf die Varianten-Route (diese liefert die Variante oder fällt
     * sicher auf das – beim Upload gekappte – Original zurück); das volle Original
     * wird nie direkt verlinkt.
     */
    public function getSrcAttribute(): ?array
    {
        if ($this->type !== 'image') {
            return null;
        }

        $version = $this->updated_at?->timestamp ?? 1;

        $build = function (string $size) use ($version) {
            return route('media.variant', ['media' => $this->id, 'variant' => $size]) . '?v=' . $version;
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
