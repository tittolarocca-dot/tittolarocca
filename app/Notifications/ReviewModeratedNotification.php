<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewModeratedNotification extends Notification
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
                ->subject('Deine Bewertung wurde freigeschaltet')
                ->line('Deine Bewertung für **' . $profile->display_name . '** wurde geprüft und ist jetzt öffentlich sichtbar.')
                ->action('Bewertung ansehen', $url)
                ->line('Danke für dein Feedback!');
        }

        return (new MailMessage)
            ->subject('Deine Bewertung wurde nicht freigeschaltet')
            ->line('Deine Bewertung für **' . $profile->display_name . '** wurde leider nicht freigeschaltet, da sie unseren Bewertungsrichtlinien nicht entspricht.')
            ->line('Du kannst eine neue, angepasste Bewertung verfassen.')
            ->action('Zum Profil', $url);
    }
}
