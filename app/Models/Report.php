<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_user_id', 'target_type', 'target_id',
        'reason', 'status', 'admin_note',
    ];

    public function reporter() { return $this->belongsTo(User::class, 'reporter_user_id'); }
}
