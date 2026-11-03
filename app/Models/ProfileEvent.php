<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ein einzelnes, zeitgestempeltes Ereignis auf einem Profil (Aufruf oder
 * Kontakt-Klick). Grundlage der Inserenten-Statistik mit Datumsbereichen.
 */
class ProfileEvent extends Model
{
    public const TYPE_VIEW     = 'view';
    public const TYPE_WHATSAPP = 'whatsapp';
    public const TYPE_CALL     = 'call';
    public const TYPE_TELEGRAM = 'telegram';
    public const TYPE_WEBSITE  = 'website';

    /** Vom öffentlichen Klick-Endpoint akzeptierte Kontakt-Typen. */
    public const CLICK_TYPES = [
        self::TYPE_WHATSAPP,
        self::TYPE_CALL,
        self::TYPE_TELEGRAM,
        self::TYPE_WEBSITE,
    ];

    // Nur created_at, kein updated_at (append-only Event-Log).
    public const UPDATED_AT = null;

    protected $fillable = ['profile_id', 'type', 'visitor_user_id', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function profile() { return $this->belongsTo(Profile::class); }
}
