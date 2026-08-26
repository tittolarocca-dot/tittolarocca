<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    protected $fillable = [
        'from_user_id', 'to_user_id', 'profile_id',
        'body', 'read_at',
        'ppv_media_path', 'ppv_media_type', 'ppv_price_chf', 'ppv_media_mode',
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

    /** Hat diese Nachricht ein Foto/Video (egal welcher Freigabe-Modus)? */
    public function hasMedia(): bool
    {
        return $this->ppv_media_type !== null;
    }

    /** Muss das Mitglied den Inhalt erst freischalten (Online-Zahlung ODER manuelle Freigabe)? */
    public function requiresUnlock(): bool
    {
        return in_array($this->ppv_media_mode, ['paid', 'manual'], true);
    }

    /** Gesperrter Inhalt mit Online-Zahlung (Stripe). */
    public function isPaidOnline(): bool
    {
        return $this->ppv_media_mode === 'paid';
    }

    /** Gesperrter Inhalt, den die Inserentin manuell freigibt (z. B. nach TWINT). */
    public function isManual(): bool
    {
        return $this->ppv_media_mode === 'manual';
    }

    /**
     * Rückwärtskompatibel: „PPV" = jeder gesperrte Inhalt, der freigeschaltet
     * werden muss. Wird an mehreren Stellen als Zugriffsschranke genutzt.
     */
    public function isPpv(): bool
    {
        return $this->requiresUnlock();
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
        if ($this->requiresUnlock()) {
            $type = $this->ppv_media_type === 'video' ? '🎬' : '📷';
            return $this->isPaidOnline()
                ? "{$type} Bezahlter Inhalt (CHF {$this->ppv_price_chf})"
                : "{$type} Gesperrter Inhalt";
        }
        if ($this->hasMedia()) {
            return $this->ppv_media_type === 'video' ? '🎬 Video' : '📷 Foto';
        }
        return $this->body ?? '';
    }
}
