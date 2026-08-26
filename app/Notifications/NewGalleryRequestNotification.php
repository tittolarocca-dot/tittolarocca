<?php

namespace App\Notifications;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewGalleryRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public Profile $profile, public string $memberName) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Neue Anfrage für deine privaten Bilder · booklola.ch')
            ->greeting('Neue Foto-Anfrage!')
            ->line('**' . $this->memberName . '** möchte deine privaten Bilder sehen.')
            ->line('Du entscheidest, ob du den Zugang freigibst.')
            ->action('Anfrage ansehen', route('inserat.gallery.requests'))
            ->line('booklola.ch');
    }
}
