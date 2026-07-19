<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditBalance extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'total_granted', 'total_spent', 'total_purchased', 'total_bonus',
    ];

    protected $casts = [
        'balance'         => 'integer',
        'total_granted'   => 'integer',
        'total_spent'     => 'integer',
        'total_purchased' => 'integer',
        'total_bonus'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
