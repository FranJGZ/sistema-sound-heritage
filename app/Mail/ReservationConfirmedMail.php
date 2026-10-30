<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {}

    public function envelope(): Envelope
    {
        $resCode = '#' . str_pad((string)$this->reservation->id, 5, '0', STR_PAD_LEFT);
        return new Envelope(
            subject: "¡Tu Reserva {$resCode} ha sido Confirmada! - Auditorio Sound Heritage"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-confirmed',
            with: [
                'reservation' => $this->reservation,
            ]
        );
    }
}

