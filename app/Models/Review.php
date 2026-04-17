<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'reviewer_user_id', 'profile_id', 'platform_subscription_id',
        'stars', 'comment', 'status', 'admin_note',
        'inserent_reply', 'reply_status', 'reviewed_at',
    ];

    protected $casts = [
        'stars'       => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_user_id'); }
    public function profile()  { return $this->belongsTo(Profile::class); }
    public function subscription() { return $this->belongsTo(PlatformSubscription::class, 'platform_subscription_id'); }

    public function isApproved(): bool { return $this->status === 'approved'; }
}
