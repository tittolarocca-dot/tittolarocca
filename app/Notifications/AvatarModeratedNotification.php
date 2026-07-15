<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AvatarModeratedNotification extends Notification
{
    use Queueable;

    /** @param string $decision 'approved'|'rejected' */
    public function __construct(public string $decision) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('konto.account.edit');

        if ($this->decision === 'approved') {
            return (new MailMessage)
                ->subject('Dein Profilfoto wurde freigeschaltet')
                ->line('Dein Profilfoto wurde geprüft und ist jetzt sichtbar.')
                ->action('Zum Profil', $url);
        }

        return (new MailMessage)
            ->subject('Dein Profilfoto wurde nicht freigeschaltet')
            ->line('Dein Profilfoto wurde leider nicht freigeschaltet, da es unseren Bildregeln nicht entspricht.')
            ->line('Bitte lade ein anderes Foto hoch. Genitalien-Fotos sind nicht erlaubt.')
            ->action('Neues Foto hochladen', $url);
    }
}
