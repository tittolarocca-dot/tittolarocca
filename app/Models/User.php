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
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'blocked_at'        => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isInserent(): bool { return $this->role === 'inserent'; }

    public function profile()  { return $this->hasOne(Profile::class); }
    public function messages() { return $this->hasMany(Message::class, 'from_user_id'); }
    public function platformSubscriptions() { return $this->hasMany(PlatformSubscription::class, 'subscriber_user_id'); }

    public function isSubscribedTo(Profile $profile): bool
    {
        return $this->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->exists();
    }
}
