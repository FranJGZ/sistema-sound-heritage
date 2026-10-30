<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $temporaryPassword = null,
        public string $origin = 'web' // 'web' o 'presencial'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a Sound Heritage! Confirmación de cuenta'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-user',
            with: [
                'user'              => $this->user,
                'temporaryPassword' => $this->temporaryPassword,
                'origin'            => $this->origin,
                'loginUrl'          => route('login'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

