<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, SetLocale::SUPPORTED)) {
            $locale = SetLocale::DEFAULT;
        }

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        // Redirect back, replacing old locale segment in the URL if present
        $previous = url()->previous('/');
        $pattern  = '#^(https?://[^/]+)/(' . implode('|', SetLocale::SUPPORTED) . ')(/|$)#';
        $replaced = preg_replace($pattern, '$1/' . $locale . '$3', $previous);

        return redirect($replaced ?? $previous);
    }
}
