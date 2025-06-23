<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionInformeEstudiante extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombreEstudiante;
    public string $fechaHora;
    public string $nombreRemitente;
    public string $cargoRemitente;
    public string $fromEmail;
    public string $fromName;

    public function __construct(
         string $fromEmail,
         string $fromName, 
         string $nombreEstudiante, 
         string $fechaHora, 
         string $nombreRemitente, 
         string $cargoRemitente)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->nombreEstudiante = $nombreEstudiante;
        $this->fechaHora = $fechaHora;
        $this->nombreRemitente = $nombreRemitente;
        $this->cargoRemitente = $cargoRemitente;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->fromName),
            subject: 'Recordatorio de sustentación de informe de prácticas',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-informe-estudiante',
            with: [
                'nombreEstudiante' => $this->nombreEstudiante,
                'fechaHora' => $this->fechaHora,
                'remitente' => $this->nombreRemitente,
                'cargoRemitente' => $this->cargoRemitente,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
