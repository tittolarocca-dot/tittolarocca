<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ein Admin-Bulk-Import (CSV). Die dabei neu erstellten User zeigen über
 * users.import_batch_id hierher zurück.
 */
class ImportBatch extends Model
{
    protected $fillable = [
        'admin_id', 'filename', 'total', 'created_count', 'skipped_count', 'error_count',
    ];

    protected $casts = [
        'total'         => 'integer',
        'created_count' => 'integer',
        'skipped_count' => 'integer',
        'error_count'   => 'integer',
    ];

    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }

    /** Die in diesem Import neu erstellten Inserenten-User. */
    public function users() { return $this->hasMany(User::class, 'import_batch_id'); }

    /** Die Profile dieses Imports (über ihre User). */
    public function profiles()
    {
        return Profile::whereIn('user_id', $this->users()->select('id'));
    }
}
