<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Öffentliche Medien: eigene, stateless Route-Gruppe (nur Binding),
            // damit die Antworten kein Set-Cookie tragen und CDN-cachebar sind.
            \Illuminate\Support\Facades\Route::middleware([
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ])->group(__DIR__.'/../routes/media_public.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hinter Cloudflare: X-Forwarded-Proto/For vertrauen, damit HTTPS
        // korrekt erkannt wird (sonst http-URLs auf einer https-Seite).
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\PreLaunch::class,
            \App\Http\Middleware\TrackActivity::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
