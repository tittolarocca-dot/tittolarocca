<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'fan_user_id',
        'profile_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'type',
        'gross_amount_chf',
        'platform_fee_chf',
        'creator_net_chf',
        'currency',
        'status',
    ];

    protected $casts = [
        'gross_amount_chf' => 'decimal:2',
        'platform_fee_chf' => 'decimal:2',
        'creator_net_chf'  => 'decimal:2',
    ];

    public function fan()     { return $this->belongsTo(User::class, 'fan_user_id'); }
    public function profile() { return $this->belongsTo(Profile::class); }
}
