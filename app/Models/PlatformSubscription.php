<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSubscription extends Model
{
    protected $fillable = [
        'subscriber_user_id', 'profile_id', 'stripe_subscription_id',
        'amount_chf', 'status', 'current_period_start', 'current_period_end', 'cancelled_at',
    ];
    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'cancelled_at'         => 'datetime',
        'amount_chf'           => 'decimal:2',
    ];

    public function subscriber() { return $this->belongsTo(User::class, 'subscriber_user_id'); }
    public function profile()    { return $this->belongsTo(Profile::class); }
    public function isActive(): bool { return $this->status === 'active' && $this->current_period_end->isFuture(); }
}
