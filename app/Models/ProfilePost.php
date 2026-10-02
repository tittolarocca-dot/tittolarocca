<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePost extends Model
{
    protected $fillable = [
        'profile_id', 'author_user_id', 'text', 'post_type', 'visibility', 'published_at',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** Verknüpfte Medien (bestehende Media-Zeilen mit context='feed'). */
    public function media()
    {
        return $this->belongsToMany(Media::class, 'profile_post_media', 'post_id', 'media_id')
            ->withPivot('sort_order')
            ->orderBy('profile_post_media.sort_order');
    }

    /** Mitglieder, die diesen Beitrag geliked haben. */
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'profile_post_likes', 'post_id', 'user_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Darf $user diesen Beitrag (und seine Medien) sehen?
     * Serverseitige Zugriffskontrolle – NIE nur im Frontend verstecken.
     * Erwartet eine geladene `profile`-Relation.
     */
    public function isVisibleTo(?User $user): bool
    {
        $isOwner = $user && $this->profile && $this->profile->user_id === $user->id;

        if (! $this->isPublished()) {
            return $isOwner; // Entwürfe nur für die Inhaberin
        }

        return match ($this->visibility) {
            'public'    => true,
            'followers' => $isOwner || ($user && $user->follows()->where('profile_id', $this->profile_id)->exists()),
            'private'   => $isOwner,
            default     => false,
        };
    }

}
