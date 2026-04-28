<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpvPurchase extends Model
{
    protected $fillable = [
        'message_id', 'buyer_user_id', 'amount_chf', 'status', 'stripe_session_id', 'paid_at',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function message() { return $this->belongsTo(Message::class); }
    public function buyer()   { return $this->belongsTo(User::class, 'buyer_user_id'); }
}
