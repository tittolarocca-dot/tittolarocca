<?php

namespace App\Mail;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Profile $profile,
        public User $subscriber,
        public float $amountChf
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Neues Abonnement – ' . $this->profile->display_name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.new-subscription');
    }
}
