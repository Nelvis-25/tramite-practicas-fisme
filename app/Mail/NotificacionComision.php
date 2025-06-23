<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionComision extends Mailable
{
    use Queueable, SerializesModels;

    public string $fromEmail;
    public string $fromName;
    public string $nombreIntegrante;
    public string $cargoIntegrante;
    public array $sustentaciones;

    public function __construct($fromEmail, $fromName, $nombreIntegrante, $cargoIntegrante, array $sustentaciones)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->nombreIntegrante = $nombreIntegrante;
        $this->cargoIntegrante = $cargoIntegrante;
        $this->sustentaciones = $sustentaciones;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->fromName),
            subject: 'Recordatorio de sustentaciones para mañana',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-comision',
            with: [
                'nombreIntegrante' => $this->nombreIntegrante,
                'cargoIntegrante' => $this->cargoIntegrante,
                'sustentaciones' => $this->sustentaciones,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}