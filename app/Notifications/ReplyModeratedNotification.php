<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReplyModeratedNotification extends Notification
{
    use Queueable;

    /** @param string $decision 'approved'|'rejected' */
    public function __construct(public Review $review, public string $decision) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $profile = $this->review->profile;
        $url     = route('profile.show', $profile->slug);

        if ($this->decision === 'approved') {
            return (new MailMessage)
                ->subject('Deine Antwort wurde freigeschaltet')
                ->line('Deine Antwort auf eine Bewertung von **' . $profile->display_name . '** ist jetzt öffentlich sichtbar.')
                ->action('Zum Profil', $url);
        }

        return (new MailMessage)
            ->subject('Deine Antwort wurde nicht freigeschaltet')
            ->line('Deine Antwort auf eine Bewertung wurde leider nicht freigeschaltet, da sie unseren Richtlinien nicht entspricht.')
            ->line('Du kannst eine neue, angepasste Antwort verfassen.')
            ->action('Zum Profil', $url);
    }
}
