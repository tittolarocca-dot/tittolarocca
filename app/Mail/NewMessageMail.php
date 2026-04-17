<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $sender, public string $preview) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Neue Nachricht von ' . $this->sender->name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.new-message');
    }
}
