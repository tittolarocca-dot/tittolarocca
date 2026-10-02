<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aktualisiert `users.last_active_at` für eingeloggte Nutzer – gedrosselt auf
 * höchstens einmal alle 5 Minuten, damit nicht jeder Request schreibt.
 * Schreibt per Query-Builder (kein Model-Event, kein `updated_at`-Churn).
 */
class TrackActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $last = $user->last_active_at;
            if (! $last || $last->diffInMinutes(now()) >= 5) {
                User::whereKey($user->id)->update(['last_active_at' => now()]);
                $user->last_active_at = now();
            }
        }

        return $next($request);
    }
}
