<?php

namespace App\Notifications;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GalleryRequestApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Profile $profile) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Zugang freigegeben · booklola.ch')
            ->greeting('Gute Neuigkeiten!')
            ->line('**' . $this->profile->display_name . '** hat dir Zugang zu den privaten Bildern gegeben.')
            ->action('Profil ansehen', route('profile.show', $this->profile->slug))
            ->line('booklola.ch');
    }
}
