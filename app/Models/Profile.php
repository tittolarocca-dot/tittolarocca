<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'slug', 'display_name', 'description',
        'city_id', 'category_id', 'age',
        'nationality', 'height_cm', 'eye_color', 'smoking', 'tattoo',
        'intimate_area', 'body_type',
        'gender', 'origin', 'weight_kg', 'cup_size', 'breast_type', 'has_video',
        'languages',
        'whatsapp_number_encrypted', 'telegram_username', 'address', 'website',
        'subscription_price_chf',
        'launch_gallery_free',
        'blocked_countries',
        'status', 'listing_expires_at', 'featured_until', 'pushed_at',
        'stripe_product_id', 'stripe_price_id',
        'total_subscribers', 'total_views',
        'verification_status', 'verification_photo',
        'verification_rejected_reason',
        'verification_submitted_at', 'verification_reviewed_at',
        // Veriff (Identität & Alter) – nur Metadaten/Status
        'identity_verification_status', 'identity_rejected_reason',
        'identity_submitted_at', 'identity_verified_at', 'age_verified_at',
        'veriff_session_id',
    ];

    protected $casts = [
        'listing_expires_at'        => 'datetime',
        'featured_until'            => 'datetime',
        'pushed_at'                 => 'datetime',
        'verification_submitted_at' => 'datetime',
        'verification_reviewed_at'  => 'datetime',
        'identity_submitted_at'     => 'datetime',
        'identity_verified_at'      => 'datetime',
        'age_verified_at'           => 'datetime',
        'subscription_price_chf'    => 'decimal:2',
        'launch_gallery_free'       => 'boolean',
        'blocked_countries'         => 'array',
        'smoking'                   => 'boolean',
        'tattoo'                    => 'boolean',
        'has_video'                 => 'boolean',
        'languages'                 => 'array',
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

    /** Ist das Inserat für Besucher aus diesem Land (ISO-2) gesperrt? */
    public function isBlockedInCountry(?string $code): bool
    {
        if (! $code) {
            return false;
        }
        $blocked = array_map('strtoupper', $this->blocked_countries ?? []);
        return in_array(strtoupper($code), $blocked, true);
    }
    public function isExpired(): bool { return $this->listing_expires_at?->isPast() ?? true; }

    public function user()         { return $this->belongsTo(User::class); }
    public function city()         { return $this->belongsTo(City::class); }
    public function category()     { return $this->belongsTo(Category::class); }
    public function categories()   { return $this->belongsToMany(Category::class, 'category_profile')->withTimestamps(); }
    public function tags()         { return $this->belongsToMany(Tag::class, 'profile_tags'); }
    public function favoritedBy()  { return $this->belongsToMany(User::class, 'favorites')->withTimestamps(); }
    public function likedBy()      { return $this->belongsToMany(User::class, 'likes')->withTimestamps(); }
    public function media()        { return $this->hasMany(Media::class)->orderBy('sort_order'); }
    public function publicMedia()  { return $this->hasMany(Media::class)->where('visibility', 'public')->where('status', 'approved'); }
    public function privateMedia() { return $this->hasMany(Media::class)->where('visibility', 'private')->where('status', 'approved'); }
    public function subscriptions(){ return $this->hasMany(PlatformSubscription::class); }
    public function reviews()      { return $this->hasMany(Review::class); }
    public function approvedReviews() { return $this->hasMany(Review::class)->where('status', 'approved'); }
    public function listingOrders(){ return $this->hasMany(ListingOrder::class); }

    /** Manuelle Foto-Verifizierung bestätigt. */
    public function isPhotoVerified(): bool { return $this->verification_status === 'approved'; }

    /** Identität & Alter über Veriff bestätigt. */
    public function isIdentityVerified(): bool { return $this->identity_verification_status === 'approved'; }

    /** Beide Prüfungen bestätigt. */
    public function isFullyVerified(): bool { return $this->isPhotoVerified() && $this->isIdentityVerified(); }
}
