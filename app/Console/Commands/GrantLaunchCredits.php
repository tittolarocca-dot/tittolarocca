<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CreditService;
use Illuminate\Console\Command;

class GrantLaunchCredits extends Command
{
    protected $signature = 'credits:grant-launch {--force : Auch ausführen, wenn LAUNCH_MODE=false}';

    protected $description = 'Schreibt allen bestehenden Inserentinnen einmalig den Launch-Bonus gut (ohne doppelt zu buchen).';

    public function handle(CreditService $credits): int
    {
        if (! config('features.launch_mode') && ! $this->option('force')) {
            $this->warn('LAUNCH_MODE ist false. Mit --force trotzdem ausführen.');
            return self::FAILURE;
        }

        $amount  = (int) config('features.launch_credits_initial', 10);
        $granted = 0;
        $skipped = 0;

        User::where('role', 'inserent')->orderBy('id')->chunkById(200, function ($users) use ($credits, $amount, &$granted, &$skipped) {
            foreach ($users as $user) {
                if ($credits->hasTransactionType($user, 'initial_launch_bonus')) {
                    $skipped++;
                    continue;
                }
                // grant() ist über 'once' idempotent und ignoriert LAUNCH_MODE (--force-tauglich).
                $credits->grant($user, $amount, 'initial_launch_bonus', [
                    'once'        => true,
                    'description' => 'Launch-Bonus (nachträglich via credits:grant-launch)',
                ]);
                $granted++;
                $this->line("  + {$user->email} → +{$amount} Launch-Credits");
            }
        });

        $this->info("Fertig. Neu gutgeschrieben: {$granted}, bereits vorhanden (übersprungen): {$skipped}.");
        return self::SUCCESS;
    }
}
