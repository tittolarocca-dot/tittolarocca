<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Models\PlatformSubscription;
use App\Models\Profile;
use Illuminate\Console\Command;

class GenerateMonthlyPayouts extends Command
{
    protected $signature   = 'payouts:generate {--month= : YYYY-MM format, defaults to last month}';
    protected $description = 'Monatliche Auszahlungen für alle aktiven Inserenten berechnen';

    public function handle(): void
    {
        $month = $this->option('month')
            ? now()->createFromFormat('Y-m', $this->option('month'))
            : now()->subMonth();

        $periodStart = $month->startOfMonth()->toDateString();
        $periodEnd   = $month->endOfMonth()->toDateString();

        $commissionRate = (float) config('platform.commission_percent', 20) / 100;

        $this->info("Generiere Auszahlungen für {$month->format('M Y')}...");

        // Sum all active subscription revenue per profile for the period
        $revenues = PlatformSubscription::where('status', 'active')
            ->whereBetween('current_period_start', [$periodStart, $periodEnd])
            ->selectRaw('profile_id, SUM(amount_chf) as total_chf')
            ->groupBy('profile_id')
            ->get();

        $minimum = (float) config('platform.payout_minimum_chf', 50);
        $created = 0;

        foreach ($revenues as $revenue) {
            if ($revenue->total_chf < $minimum) {
                $this->line("  Profil {$revenue->profile_id}: CHF {$revenue->total_chf} < Minimum CHF {$minimum} — übersprungen");
                continue;
            }

            // Idempotency: skip if payout already exists for this period
            $exists = Payout::where('profile_id', $revenue->profile_id)
                ->where('period_start', $periodStart)
                ->exists();

            if ($exists) continue;

            $gross      = round($revenue->total_chf, 2);
            $commission = round($gross * $commissionRate, 2);
            $net        = round($gross - $commission, 2);

            Payout::create([
                'profile_id'       => $revenue->profile_id,
                'gross_amount_chf' => $gross,
                'commission_chf'   => $commission,
                'net_amount_chf'   => $net,
                'status'           => 'pending',
                'period_start'     => $periodStart,
                'period_end'       => $periodEnd,
            ]);

            $created++;
            $this->line("  Profil {$revenue->profile_id}: CHF {$net} netto erstellt.");
        }

        $this->info("Fertig. {$created} Auszahlungen erstellt.");
    }
}
