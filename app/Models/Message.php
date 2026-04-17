<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    protected $fillable = [
        'from_user_id', 'to_user_id', 'profile_id', 'body_encrypted', 'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function from() { return $this->belongsTo(User::class, 'from_user_id'); }
    public function to()   { return $this->belongsTo(User::class, 'to_user_id'); }
    public function profile() { return $this->belongsTo(Profile::class); }

    public function setBodyAttribute(string $value): void
    {
        $this->attributes['body_encrypted'] = Crypt::encryptString($value);
    }

    public function getBodyAttribute(): ?string
    {
        return $this->attributes['body_encrypted']
            ? Crypt::decryptString($this->attributes['body_encrypted'])
            : null;
    }
}
