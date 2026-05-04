<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;

class ExpireFreeListings extends Command
{
    protected $signature   = 'listings:expire-free';
    protected $description = 'Set free listings to expired status after their listing_expires_at date';

    public function handle(): void
    {
        $expired = Profile::where('status', 'active')
            ->where('listing_expires_at', '<', now())
            ->whereDoesntHave('listingOrders', fn ($q) => $q->where('amount_chf', '>', 0))
            ->update(['status' => 'expired']);

        $this->info("Expired {$expired} free listing(s).");
    }
}
