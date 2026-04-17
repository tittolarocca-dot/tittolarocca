<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingOrder extends Model
{
    protected $fillable = [
        'user_id', 'profile_id', 'listing_package_id',
        'amount_chf', 'currency', 'status',
        'stripe_payment_intent_id', 'paid_at', 'expires_at',
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'expires_at' => 'datetime',
        'amount_chf' => 'decimal:2',
    ];

    public function user()           { return $this->belongsTo(User::class); }
    public function profile()        { return $this->belongsTo(Profile::class); }
    public function listingPackage() { return $this->belongsTo(ListingPackage::class); }
}
