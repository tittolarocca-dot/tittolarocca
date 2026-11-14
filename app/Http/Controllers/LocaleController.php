<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, SetLocale::SUPPORTED)) {
            $locale = SetLocale::DEFAULT;
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        // MANUELLE Wahl dauerhaft merken, damit sie bei späteren Besuchen Vorrang
        // vor der automatischen Erkennung hat und nie überschrieben wird:
        //  - Cookie (1 Jahr) → pro Gerät, auch für Gäste
        //  - users.locale    → geräteübergreifend für eingeloggte Nutzer:innen
        Cookie::queue(cookie('locale', $locale, 60 * 24 * 365, null, null, null, true, false, 'Lax'));
        if ($user = $request->user()) {
            if ($user->locale !== $locale) {
                $user->forceFill(['locale' => $locale])->save();
            }
        }

        // Redirect back, replacing old locale segment in the URL if present
        $previous = url()->previous('/');
        $pattern  = '#^(https?://[^/]+)/(' . implode('|', SetLocale::SUPPORTED) . ')(/|$)#';
        $replaced = preg_replace($pattern, '$1/' . $locale . '$3', $previous);

        return redirect($replaced ?? $previous);
    }
}
