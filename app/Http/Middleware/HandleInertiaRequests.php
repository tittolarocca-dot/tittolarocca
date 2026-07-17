<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'    => $request->user()->id,
                    'name'  => $request->user()->name,
                    'email' => $request->user()->email,
                    'role'  => $request->user()->role,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'cities'     => fn () => \App\Models\City::where('is_active', true)->orderBy('sort_order')->get(),
            'categories' => fn () => \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get(),

            // i18n
            'locale'           => $locale,
            'supportedLocales' => SetLocale::SUPPORTED,
            'translations'     => fn () => [
                'nav'       => trans('nav'),
                'home'      => trans('home'),
                'profile'   => trans('profile'),
                'auth'      => trans('auth'),
                'dashboard' => trans('dashboard'),
                'inserent'  => trans('inserent'),
            ],
        ];
    }
}
