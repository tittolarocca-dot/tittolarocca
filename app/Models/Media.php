<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'profile_id', 'type', 'storage_path', 'thumbnail_path',
        'visibility', 'status', 'rejection_reason', 'sort_order',
        'filesize_bytes', 'duration_seconds',
    ];

    protected $casts = [
        'sort_order'      => 'integer',
        'filesize_bytes'  => 'integer',
        'duration_seconds'=> 'integer',
    ];

    public function profile()   { return $this->belongsTo(Profile::class); }
    public function isPublic()  { return $this->visibility === 'public'; }
    public function isApproved(){ return $this->status === 'approved'; }
}
