<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Manuelle 18+ Alters- & Identitätsprüfung (Ausweis + Identifikationsvideo).
 *
 * Sicherheit/Datenschutz:
 *  - first_name/last_name werden verschlüsselt gespeichert (encrypted cast).
 *  - document_path/video_path zeigen auf die PRIVATE Disk (storage/app/private);
 *    diese Dateien sind nie öffentlich und werden nur über autorisierte
 *    Controller-Endpunkte (Eigentümer oder Admin/Moderator) ausgeliefert.
 */
class AgeVerification extends Model
{
    public const STATUS_NOT_SUBMITTED = 'not_submitted';
    public const STATUS_PENDING       = 'pending';
    public const STATUS_VERIFIED      = 'verified';
    public const STATUS_REJECTED      = 'rejected';

    protected $fillable = [
        'profile_id',
        'first_name', 'last_name', 'birth_date',
        'document_path', 'document_mime', 'video_path', 'video_mime',
        'status', 'rejection_reason',
        'submitted_at', 'reviewed_at', 'reviewed_by',
        'purge_after', 'files_purged_at',
    ];

    protected $casts = [
        // Vor-/Nachname verschlüsselt im Ruhezustand.
        'first_name'      => 'encrypted',
        'last_name'       => 'encrypted',
        'birth_date'      => 'date',
        'submitted_at'    => 'datetime',
        'reviewed_at'     => 'datetime',
        'purge_after'     => 'datetime',
        'files_purged_at' => 'datetime',
    ];

    // Sensible Attribute niemals versehentlich in Logs/Arrays serialisieren.
    protected $hidden = [
        'first_name', 'last_name', 'document_path', 'video_path',
    ];

    public function profile()    { return $this->belongsTo(Profile::class); }
    public function reviewer()   { return $this->belongsTo(User::class, 'reviewed_by'); }

    /** Errechnetes Alter in vollen Jahren (oder null, wenn kein Datum). */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }
        return Carbon::parse($this->birth_date)->age;
    }

    public function isAdult(): bool
    {
        return $this->age !== null && $this->age >= 18;
    }

    public function isVerified(): bool   { return $this->status === self::STATUS_VERIFIED; }
    public function isPending(): bool    { return $this->status === self::STATUS_PENDING; }
    public function isRejected(): bool   { return $this->status === self::STATUS_REJECTED; }

    public function hasDocument(): bool  { return (bool) $this->document_path; }
    public function hasVideo(): bool     { return (bool) $this->video_path; }

    /** Alles vorhanden, um einreichen zu können. */
    public function isComplete(): bool
    {
        return filled($this->first_name)
            && filled($this->last_name)
            && $this->birth_date
            && $this->hasDocument()
            && $this->hasVideo();
    }
}
