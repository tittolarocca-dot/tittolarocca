<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Support\SeoData;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ein SEO-Container pro Request; Controller füllen ihn, das Root-Blade liest ihn.
        $this->app->singleton(SeoData::class);

        // Admin-Login immer aufs Dashboard leiten (nicht auf eine gemerkte URL).
        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Http\Responses\FilamentLoginResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        // $seo im Root-Layout (app.blade.php) verfügbar machen.
        View::composer('app', fn ($view) => $view->with('seo', app(SeoData::class)));

        // Bestätigungsmail auf Deutsch (Booklola-Branding)
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Bestätige deine E-Mail-Adresse · booklola.ch')
                ->greeting('Willkommen bei booklola.ch!')
                ->line('Danke für deine Registrierung. Bitte bestätige deine E-Mail-Adresse, um dein Konto zu aktivieren.')
                ->action('E-Mail-Adresse bestätigen', $url)
                ->line('Der Link ist aus Sicherheitsgründen zeitlich begrenzt gültig.')
                ->line('Wenn du dich nicht bei booklola.ch registriert hast, kannst du diese E-Mail einfach ignorieren.')
                ->salutation('Herzliche Grüsse
Dein booklola.ch-Team');
        });
    }
}
