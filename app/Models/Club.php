<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Club extends Model
{
    public const CATEGORIES = ['Club', 'Studio', 'Bordell', 'Saunaclub', 'Massage', 'Kontaktbar', 'Sonstiges'];
    public const STATUSES   = ['active', 'pending', 'inactive', 'rejected'];

    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    protected $fillable = [
        'name', 'slug', 'description', 'canton', 'city', 'postal_code', 'address',
        'latitude', 'longitude', 'category', 'phone', 'email', 'website_url',
        'opening_hours', 'is_premium', 'is_verified', 'status',
        'rating_average', 'rating_count', 'website_clicks', 'profile_views',
    ];

    protected $casts = [
        'opening_hours'  => 'array',
        'is_premium'     => 'boolean',
        'is_verified'    => 'boolean',
        'latitude'       => 'float',
        'longitude'      => 'float',
        'rating_average' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Club $club) {
            if (! empty($club->slug)) {
                return;
            }
            $base = \Illuminate\Support\Str::slug($club->name) ?: 'club';

            // Kollision mit Kanton-Slugs vermeiden (die Route /clubs/{kanton} würde sie sonst abfangen)
            $cantonSlugs = array_column(config('cantons', []), 'slug');
            if (in_array($base, $cantonSlugs, true)) {
                $base .= '-club';
            }

            $slug = $base;
            $i    = 2;
            while (static::where('slug', $slug)->where('id', '!=', $club->id ?? 0)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $club->slug = $slug;
        });
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function getCantonNameAttribute(): string
    {
        return config("cantons.{$this->canton}.name", $this->canton);
    }

    public function getCantonSlugAttribute(): ?string
    {
        return config("cantons.{$this->canton}.slug");
    }

    // ── Öffnungszeiten ──────────────────────────────────────────────────────
    // opening_hours: [ {"day":"mon","from":"18:00","to":"04:00"}, … ]
    // from === to  → 24 Stunden geöffnet an diesem Tag.

    private function entriesForDay(string $day): array
    {
        return array_values(array_filter(
            $this->opening_hours ?? [],
            fn ($e) => ($e['day'] ?? null) === $day
        ));
    }

    private function nowZurich(): Carbon
    {
        return now('Europe/Zurich');
    }

    public function isOpenNow(): bool
    {
        $now   = $this->nowZurich();
        $t     = $now->format('H:i');
        $today = self::DAYS[$now->dayOfWeekIso - 1];
        $prev  = self::DAYS[($now->dayOfWeekIso + 5) % 7];

        foreach ($this->entriesForDay($today) as $e) {
            [$from, $to] = [$e['from'] ?? null, $e['to'] ?? null];
            if (! $from || ! $to) continue;
            if ($from === $to) return true;                  // 24h
            if ($from < $to) { if ($t >= $from && $t < $to) return true; }
            elseif ($t >= $from) return true;                // über Mitternacht (heute Abend)
        }
        // Gestern gestartete Nacht-Öffnung, die in den heutigen Morgen reicht
        foreach ($this->entriesForDay($prev) as $e) {
            [$from, $to] = [$e['from'] ?? null, $e['to'] ?? null];
            if ($from && $to && $from > $to && $t < $to) return true;
        }
        return false;
    }

    public function isOpenToday(): bool
    {
        return count($this->entriesForDay(self::DAYS[$this->nowZurich()->dayOfWeekIso - 1])) > 0;
    }

    public function isOpenWeekend(): bool
    {
        return count($this->entriesForDay('sat')) > 0 || count($this->entriesForDay('sun')) > 0;
    }

    public function is24h(): bool
    {
        $oh = $this->opening_hours ?? [];
        if (empty($oh)) return false;
        foreach (self::DAYS as $d) {
            $e = $this->entriesForDay($d)[0] ?? null;
            if (! $e || empty($e['from']) || ($e['from'] ?? null) !== ($e['to'] ?? null)) {
                return false;
            }
        }
        return true;
    }

    /** Kurzlabel der heutigen Öffnungszeiten für die Liste. */
    public function todayLabel(): ?string
    {
        if ($this->is24h()) return '24h';
        $e = $this->entriesForDay(self::DAYS[$this->nowZurich()->dayOfWeekIso - 1])[0] ?? null;
        if (! $e) return null;
        if (($e['from'] ?? null) === ($e['to'] ?? null)) return '24h';
        return ($e['from'] ?? '') . '–' . ($e['to'] ?? '');
    }
}
