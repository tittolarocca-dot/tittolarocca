<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'profile_id', 'type', 'storage_path', 'blur_path', 'variants',
        'width', 'height', 'mime_type',
        'visibility', 'status', 'rejection_reason', 'sort_order',
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

    /** Original-Stream (auth-geprüft in MediaStreamController). */
    public function getUrlAttribute(): string
    {
        return route('media.stream', $this->id);
    }

    /**
     * Optimierte, versionierte Varianten-URLs (thumbnail/card/full).
     * Nur für Bilder; fehlende Varianten fallen auf den Original-Stream zurück.
     * Die Route ist bei privaten Medien identisch autorisiert wie das Original.
     */
    public function getSrcAttribute(): ?array
    {
        if ($this->type !== 'image') {
            return null;
        }

        $variants = $this->variants ?? [];
        $version  = $this->updated_at?->timestamp ?? 1;
        $fallback = $this->getUrlAttribute();

        $build = function (string $size) use ($variants, $version, $fallback) {
            return ! empty($variants[$size])
                ? route('media.variant', ['media' => $this->id, 'variant' => $size]) . '?v=' . $version
                : $fallback;
        };

        return [
            'thumbnail' => $build('thumbnail'),
            'card'      => $build('card'),
            'full'      => $build('full'),
        ];
    }

    public function profile()   { return $this->belongsTo(Profile::class); }
    public function isPublic()  { return $this->visibility === 'public'; }
    public function isApproved(){ return $this->status === 'approved'; }
}
