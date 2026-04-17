<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'slug', 'parent_id', 'is_active', 'sort_order'];

    public function profiles() { return $this->hasMany(Profile::class); }
}
