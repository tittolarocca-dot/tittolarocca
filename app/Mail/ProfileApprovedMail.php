<?php

namespace App\Mail;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Profile $profile) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Dein Profil ist jetzt aktiv!');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.profile-approved');
    }
}
