<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    public $timestamps = false; // Tabelle hat nur created_at (DB-Default)

    protected $fillable = [
        'admin_id', 'action', 'target_type', 'target_id', 'note', 'ip_address',
    ];

    /** Bequemes Protokollieren einer Admin-Aktion. */
    public static function record(string $action, string $targetType, int $targetId, ?string $note = null): void
    {
        static::create([
            'admin_id'    => auth()->id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'note'        => $note,
            'ip_address'  => request()->ip(),
        ]);
    }

    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}
