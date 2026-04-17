<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'profile_id', 'gross_amount_chf', 'commission_chf', 'net_amount_chf',
        'status', 'iban', 'bank_name', 'account_holder',
        'stripe_transfer_id', 'period_start', 'period_end', 'paid_at',
    ];

    protected $casts = [
        'gross_amount_chf' => 'decimal:2',
        'commission_chf'   => 'decimal:2',
        'net_amount_chf'   => 'decimal:2',
        'period_start'     => 'date',
        'period_end'       => 'date',
        'paid_at'          => 'datetime',
    ];

    public function profile() { return $this->belongsTo(Profile::class); }
}
