<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'slug', 'display_name', 'description',
        'city_id', 'category_id', 'age',
        'whatsapp_number_encrypted', 'telegram_username', 'address',
        'subscription_price_chf',
        'status', 'listing_expires_at', 'featured_until', 'pushed_at',
        'stripe_product_id', 'stripe_price_id',
        'total_subscribers', 'total_views',
        'verification_status', 'verification_photo',
        'verification_rejected_reason',
        'verification_submitted_at', 'verification_reviewed_at',
    ];

    protected $casts = [
        'listing_expires_at'        => 'datetime',
        'featured_until'            => 'datetime',
        'pushed_at'                 => 'datetime',
        'verification_submitted_at' => 'datetime',
        'verification_reviewed_at'  => 'datetime',
        'subscription_price_chf'    => 'decimal:2',
    ];

    // Encrypted WhatsApp getter/setter
    public function setWhatsappNumberAttribute(?string $value): void
    {
        $this->attributes['whatsapp_number_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getWhatsappNumberAttribute(): ?string
    {
        return $this->attributes['whatsapp_number_encrypted']
            ? Crypt::decryptString($this->attributes['whatsapp_number_encrypted'])
            : null;
    }

    public function isActive(): bool { return $this->status === 'active' && $this->listing_expires_at?->isFuture(); }
    public function isExpired(): bool { return $this->listing_expires_at?->isPast() ?? true; }

    public function user()         { return $this->belongsTo(User::class); }
    public function city()         { return $this->belongsTo(City::class); }
    public function category()     { return $this->belongsTo(Category::class); }
    public function tags()         { return $this->belongsToMany(Tag::class, 'profile_tags'); }
    public function media()        { return $this->hasMany(Media::class)->orderBy('sort_order'); }
    public function publicMedia()  { return $this->hasMany(Media::class)->where('visibility', 'public')->where('status', 'approved'); }
    public function privateMedia() { return $this->hasMany(Media::class)->where('visibility', 'private')->where('status', 'approved'); }
    public function subscriptions(){ return $this->hasMany(PlatformSubscription::class); }
    public function reviews()      { return $this->hasMany(Review::class); }
    public function approvedReviews() { return $this->hasMany(Review::class)->where('status', 'approved'); }
    public function listingOrders(){ return $this->hasMany(ListingOrder::class); }
}
