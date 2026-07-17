<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'slug', 'group'];

    /**
     * Übersetzten Namen anhand des Slugs zurückgeben (Fallback: gespeicherter
     * deutscher Name). So werden Services/Tags überall automatisch lokalisiert.
     */
    public function getNameAttribute($value)
    {
        $slug = $this->attributes['slug'] ?? null;
        if (! $slug) {
            return $value;
        }
        $key = 'services.' . $slug;
        // Ohne Sprach-Fallback: fehlt eine Übersetzung, bleibt der gespeicherte Name.
        $t = \Illuminate\Support\Facades\Lang::get($key, [], app()->getLocale(), false);
        return (is_string($t) && $t !== $key) ? $t : $value;
    }
}
