<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingPackage extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'duration_days', 'price_chf', 'features', 'is_active', 'sort_order'];
    protected $casts    = ['features' => 'array', 'price_chf' => 'decimal:2'];
}
