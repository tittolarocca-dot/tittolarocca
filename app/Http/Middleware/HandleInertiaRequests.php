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

    /**
     * Avatar-URL für die Sidebar/Header: Mitglied = freigegebener Avatar,
     * Inserent:in = erstes öffentliches Profilfoto (Thumbnail). Sonst null
     * (Frontend zeigt dann die Initiale). Wird nur bei Inertia-Seitenaufrufen
     * serialisiert, nicht bei JSON-Endpoints.
     */
    private function avatarFor(\App\Models\User $user): ?string
    {
        if ($user->role === 'member') {
            return ($user->avatar_path && $user->avatar_status === 'approved')
                ? route('mitglied.avatar', $user->id) . '?v=' . ($user->updated_at?->timestamp ?? 1)
                : null;
        }

        $profile = $user->profile()
            ->with(['publicMedia' => fn ($q) => $q->where('type', 'image')->orderBy('sort_order')])
            ->first();
        $media = $profile?->publicMedia->first();

        return $media ? ($media->src['thumbnail'] ?? $media->url) : null;
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
                    'email_verified' => (bool) $request->user()->hasVerifiedEmail(),
                    'avatar' => $this->avatarFor($request->user()),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'cities'     => fn () => \App\Models\City::where('is_active', true)->orderBy('sort_order')->get(),
            'categories' => fn () => \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get(),

            // Feature-Flags (Launch-Modus etc.) – für Preise/Buttons im Frontend
            'features' => [
                'launchMode' => (bool) config('features.launch_mode'),
                'veriff'     => (bool) config('features.veriff_enabled'),
            ],

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
                'languages' => trans('languages'),
                'gate'      => trans('gate'),
                'clubs'     => trans('clubs'),
            ],
        ];
    }
}
