<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    protected $fillable = [
        'from_user_id', 'to_user_id', 'profile_id',
        'body', 'read_at',
        'ppv_media_path', 'ppv_media_type', 'ppv_price_chf',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function from()        { return $this->belongsTo(User::class, 'from_user_id'); }
    public function to()          { return $this->belongsTo(User::class, 'to_user_id'); }
    public function profile()     { return $this->belongsTo(Profile::class); }
    public function ppvPurchases(){ return $this->hasMany(PpvPurchase::class); }

    public function setBodyAttribute(?string $value): void
    {
        $this->attributes['body_encrypted'] = $value !== null && $value !== ''
            ? Crypt::encryptString($value)
            : null;
    }

    public function getBodyAttribute(): ?string
    {
        return isset($this->attributes['body_encrypted']) && $this->attributes['body_encrypted']
            ? Crypt::decryptString($this->attributes['body_encrypted'])
            : null;
    }

    public function isPpv(): bool
    {
        return $this->ppv_media_type !== null && $this->ppv_price_chf !== null;
    }

    public function isUnlockedFor(int $userId): bool
    {
        return $this->ppvPurchases()
            ->where('buyer_user_id', $userId)
            ->where('status', 'paid')
            ->exists();
    }

    public function previewText(): string
    {
        if ($this->isPpv()) {
            $type = $this->ppv_media_type === 'video' ? '🎬' : '📷';
            return "{$type} Bezahlter Inhalt (CHF {$this->ppv_price_chf})";
        }
        return $this->body ?? '';
    }
}
