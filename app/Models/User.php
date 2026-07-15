<?php
namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'email_verified_at',
        'gender', 'age', 'height_cm', 'weight_kg', 'city_id', 'languages',
        'smoking', 'bio', 'preferences', 'deactivated_at', 'avatar_path',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'blocked_at'        => 'datetime',
            'deactivated_at'    => 'datetime',
            'languages'         => 'array',
            'smoking'           => 'boolean',
            'age'               => 'integer',
            'height_cm'         => 'integer',
            'weight_kg'         => 'integer',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isInserent(): bool { return $this->role === 'inserent'; }

    public function profile()  { return $this->hasOne(Profile::class); }
    public function city()     { return $this->belongsTo(City::class); }
    public function writtenReviews() { return $this->hasMany(Review::class, 'reviewer_user_id'); }
    public function messages() { return $this->hasMany(Message::class, 'from_user_id'); }
    public function platformSubscriptions() { return $this->hasMany(PlatformSubscription::class, 'subscriber_user_id'); }
    public function favorites() { return $this->belongsToMany(Profile::class, 'favorites')->withTimestamps(); }

    public function isSubscribedTo(Profile $profile): bool
    {
        return $this->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where('current_period_end', '>', now())
            ->exists();
    }
}
