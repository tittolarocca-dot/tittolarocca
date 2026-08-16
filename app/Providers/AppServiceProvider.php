<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

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
