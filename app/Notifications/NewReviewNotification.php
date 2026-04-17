<?php

namespace App\Notifications;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public Profile $profile, public int $stars) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Neue Bewertung für ' . $this->profile->display_name)
            ->line(str_repeat('⭐', $this->stars) . ' Neue Bewertung erhalten!')
            ->line('Ein Abonnent hat dein Profil **' . $this->profile->display_name . '** bewertet.')
            ->action('Bewertung ansehen', route('profile.show', $this->profile->slug))
            ->line('Inserate Plattform');
    }
}
