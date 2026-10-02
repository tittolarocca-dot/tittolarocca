<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Kompakte, menschenfreundliche relative Zeitangaben auf Deutsch.
 * Bewusst ohne exakte Zeitstempel – für öffentliche Profile/Feed.
 */
class RelativeTime
{
    /** z. B. "gerade eben", "vor 5 Min.", "vor 2 Std.", "gestern", "vor 3 Tagen". */
    public static function short(?CarbonInterface $dt): ?string
    {
        if (! $dt) {
            return null;
        }

        $now     = now();
        $minutes = (int) $dt->diffInMinutes($now);

        if ($minutes < 1)   return 'gerade eben';
        if ($minutes < 60)  return 'vor ' . $minutes . ' Min.';

        $hours = intdiv($minutes, 60);
        if ($hours < 24)    return 'vor ' . $hours . ' Std.';

        if ($dt->isYesterday()) return 'gestern';

        $days = intdiv($hours, 24);
        if ($days < 7)      return 'vor ' . $days . ($days === 1 ? ' Tag' : ' Tagen');

        return $dt->format('d.m.Y');
    }

    /** Online, wenn die letzte Aktivität weniger als $minutes zurückliegt. */
    public static function isOnline(?CarbonInterface $dt, int $minutes = 10): bool
    {
        return $dt !== null && (int) $dt->diffInMinutes(now()) < $minutes;
    }
}
