<?php

namespace App\Observers;

use App\Models\User;
use App\Services\CreditService;

class UserObserver
{
    public function __construct(private CreditService $credits)
    {
    }

    /**
     * Sobald ein Benutzer die Rolle "inserent" hat, erhält er im Launch-Modus
     * einmalig den Launch-Bonus. Deckt sowohl die Profil-Erstellung (setzt role
     * = inserent) als auch spätere Rollen-Änderungen durch den Admin ab.
     * grantLaunchBonus() ist idempotent → kein Doppel-Bonus bei mehrfachem Save.
     */
    public function saved(User $user): void
    {
        if ($user->role === 'inserent' && config('features.launch_mode')) {
            $this->credits->grantLaunchBonus($user);
        }
    }
}
