<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * Nach dem Admin-Login IMMER aufs Panel-Dashboard leiten – ignoriert eine
 * evtl. in der Session gemerkte "intended"-URL (z. B. /inserieren aus dem
 * Pre-Launch-Modus), die sonst zu einer falschen Weiterleitung führt.
 */
class FilamentLoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->to(Filament::getUrl());
    }
}
