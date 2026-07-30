<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubReport extends Model
{
    protected $fillable = ['club_id', 'type', 'message', 'email', 'status'];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
