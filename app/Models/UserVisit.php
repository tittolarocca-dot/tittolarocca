<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVisit extends Model
{
    protected $fillable = ['visited_user_id', 'visitor_user_id', 'last_visited_at'];

    protected $casts = ['last_visited_at' => 'datetime'];

    public function visitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visited_user_id');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visitor_user_id');
    }
}
