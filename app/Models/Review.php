<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'reviewer_user_id', 'profile_id', 'platform_subscription_id',
        'stars', 'comment', 'status', 'admin_note', 'rejection_reason',
        'inserent_reply', 'reply_status', 'reply_rejection_reason',
        'reviewed_at', 'reply_submitted_at', 'moderated_at', 'reply_moderated_at',
    ];

    protected $casts = [
        'stars'              => 'integer',
        'reviewed_at'        => 'datetime',
        'reply_submitted_at' => 'datetime',
        'moderated_at'       => 'datetime',
        'reply_moderated_at' => 'datetime',
    ];

    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_user_id'); }
    public function profile()  { return $this->belongsTo(Profile::class); }
    public function subscription() { return $this->belongsTo(PlatformSubscription::class, 'platform_subscription_id'); }

    public function isApproved(): bool      { return $this->status === 'approved'; }
    public function isReplyApproved(): bool { return $this->reply_status === 'approved'; }
    public function hasReply(): bool        { return filled($this->inserent_reply); }
}
