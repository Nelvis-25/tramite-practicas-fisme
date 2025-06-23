<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionJuradoInforme extends Mailable
{
    use Queueable, SerializesModels;

    public string $fromEmail;
    public string $fromName;
    public string $nombreJurado;
    public string $cargoJurado;
    public array $sustentaciones;

    public function __construct($fromEmail, $fromName, $nombreJurado, $cargoJurado, array $sustentaciones)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->nombreJurado = $nombreJurado;
        $this->cargoJurado = $cargoJurado;
        $this->sustentaciones = $sustentaciones;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->fromName),
            subject: 'Recordatorio de sustentaciones de informes para mañana',
        );
    }

   public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-jurado-informe',
            with: [
                'nombreJurado' => $this->nombreJurado,
                'cargoJurado' => $this->cargoJurado,
                'sustentaciones' => $this->sustentaciones,
            ],
        );
    }

  public function attachments(): array 
    {
        return [];
    }
}
