<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\ProfileEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Inserenten-Statistik: KPI-Werte (Profilaufrufe, WhatsApp-/Anruf-Klicks,
 * empfangene Nachrichten, Favoriten, Follower) für einen wählbaren
 * Datumsbereich, optional mit Vergleichszeitraum (Delta).
 *
 * Hinweis: Aufrufe/Klicks stammen aus profile_events und existieren erst ab
 * Einführung des Trackings. Nachrichten/Favoriten/Follower haben volle Historie.
 */
class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;
        if (! $profile) {
            return redirect()->route('inserat.profile.edit')->with('error', 'Bitte erstelle zuerst dein Profil.');
        }

        $range   = (string) $request->input('range', 'month');
        $compare = (string) $request->input('compare', 'none');

        [$start, $end] = $this->resolveRange($range, $request->input('from'), $request->input('to'));
        $cmp = $this->resolveCompare($compare, $request->input('compare_from'), $request->input('compare_to'));

        return inertia('Inserent/Statistics', [
            'metrics' => $this->metrics($profile->id, $user->id, $start, $end, $cmp),
            'filters' => [
                'range'        => $range,
                'compare'      => $compare,
                'from'         => $start->toDateString(),
                'to'           => $end->toDateString(),
                'compare_from' => $cmp ? $cmp[0]->toDateString() : null,
                'compare_to'   => $cmp ? $cmp[1]->toDateString() : null,
            ],
        ]);
    }

    /** Primärer Zeitraum aus der Auswahl. */
    private function resolveRange(string $range, $from, $to): array
    {
        $now = now();
        return match ($range) {
            'today'  => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
            'week'   => [$now->copy()->startOfWeek(),  $now->copy()->endOfWeek()],
            'year'   => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
            'custom' => $this->customRange($from, $to),
            default  => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /** Vergleichszeitraum (oder null = kein Vergleich). */
    private function resolveCompare(string $compare, $from, $to): ?array
    {
        $now = now();
        return match ($compare) {
            'yesterday'  => [$now->copy()->subDay()->startOfDay(),               $now->copy()->subDay()->endOfDay()],
            'prev_week'  => [$now->copy()->subWeek()->startOfWeek(),             $now->copy()->subWeek()->endOfWeek()],
            'prev_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'prev_year'  => [$now->copy()->subYear()->startOfYear(),            $now->copy()->subYear()->endOfYear()],
            'custom'     => $this->customRange($from, $to),
            default      => null,
        };
    }

    private function customRange($from, $to): array
    {
        $now = now();
        try {
            $s = $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth();
            $e = $to   ? Carbon::parse($to)->endOfDay()     : $now->copy()->endOfDay();
            if ($e->lt($s)) {
                [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
            }
            return [$s, $e];
        } catch (\Throwable) {
            return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }
    }

    /** Alle KPI-Werte für Primär- und (optional) Vergleichszeitraum. */
    private function metrics(int $profileId, int $ownerUserId, Carbon $start, Carbon $end, ?array $cmp): array
    {
        $event = fn (string $type) => fn (Carbon $s, Carbon $e) => ProfileEvent::query()
            ->where('profile_id', $profileId)->where('type', $type)
            ->whereBetween('created_at', [$s, $e])->count();

        $defs = [
            'views'     => $event(ProfileEvent::TYPE_VIEW),
            'whatsapp'  => $event(ProfileEvent::TYPE_WHATSAPP),
            'calls'     => $event(ProfileEvent::TYPE_CALL),
            'messages'  => fn (Carbon $s, Carbon $e) => DB::table('messages')
                ->where('to_user_id', $ownerUserId)->whereBetween('created_at', [$s, $e])->count(),
            'favorites' => fn (Carbon $s, Carbon $e) => DB::table('favorites')
                ->where('profile_id', $profileId)->whereBetween('created_at', [$s, $e])->count(),
            'follows'   => fn (Carbon $s, Carbon $e) => DB::table('profile_follows')
                ->where('profile_id', $profileId)->whereBetween('created_at', [$s, $e])->count(),
        ];

        $out = [];
        foreach ($defs as $key => $fn) {
            $value   = (int) $fn($start, $end);
            $compare = $cmp ? (int) $fn($cmp[0], $cmp[1]) : null;
            $out[] = [
                'key'     => $key,
                'value'   => $value,
                'compare' => $compare,
                'delta'   => $cmp !== null ? $value - $compare : null,
            ];
        }
        return $out;
    }
}
