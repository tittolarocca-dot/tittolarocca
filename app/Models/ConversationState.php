<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationState extends Model
{
    protected $fillable = [
        'user_id', 'other_user_id',
        'hidden_at', 'cleared_at', 'marked_unread_at',
    ];

    protected $casts = [
        'hidden_at'        => 'datetime',
        'cleared_at'       => 'datetime',
        'marked_unread_at' => 'datetime',
    ];

    /** Holt (oder erstellt) den State-Datensatz für die Sicht von $userId auf $otherId. */
    public static function for(int $userId, int $otherId): self
    {
        return static::firstOrCreate(['user_id' => $userId, 'other_user_id' => $otherId]);
    }

    /** Alle States eines Nutzers, indexiert nach other_user_id – fürs Listen-Rendering. */
    public static function mapFor(int $userId): \Illuminate\Support\Collection
    {
        return static::where('user_id', $userId)->get()->keyBy('other_user_id');
    }
}
