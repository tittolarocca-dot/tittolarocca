<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateGalleryRequest extends Model
{
    protected $fillable = [
        'profile_id', 'user_id', 'status', 'responded_at',
    ];

    protected $casts = ['responded_at' => 'datetime'];

    public function profile() { return $this->belongsTo(Profile::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
