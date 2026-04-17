<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'canton', 'slug', 'is_active', 'sort_order'];

    public function profiles() { return $this->hasMany(Profile::class); }
}
