<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['de', 'en', 'fr', 'it', 'es', 'hu', 'ro'];
    public const DEFAULT    = 'de';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale')
            ?? $request->session()->get('locale', self::DEFAULT);

        if (!in_array($locale, self::SUPPORTED)) {
            $locale = self::DEFAULT;
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
