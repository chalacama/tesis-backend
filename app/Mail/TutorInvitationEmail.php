<?php

namespace App\Mail;

use App\Models\CourseInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address; // <--- IMPORTANTE: Agrega esta línea
use Illuminate\Queue\SerializesModels;

class TutorInvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public CourseInvitation $invitation;
    public string $acceptUrl;
    protected array $senderConfig; // Propiedad protegida para guardar la config

    // Recibimos $senderConfig en el constructor (le ponemos un valor por defecto array vacío por si acaso)
    public function __construct(CourseInvitation $invitation, array $senderConfig = [])
    {
        $this->invitation = $invitation;
        $this->senderConfig = $senderConfig; // Guardamos la config

        $frontendUrl = config('app.frontend_url', config('app.url'));
        $this->acceptUrl = rtrim($frontendUrl, '/') . '/invitation/accept?token=' . $invitation->token;
    }

    public function envelope(): Envelope
    {
        // Definimos valores por defecto si no vienen en el array
        $fromAddress = $this->senderConfig['from'] ?? env('MAIL_FROM_ADDRESS');
        $fromName    = $this->senderConfig['name'] ?? env('MAIL_FROM_NAME');

        return new Envelope(
            // Aquí es donde sucede la magia del remitente dinámico:
            from: new Address($fromAddress, $fromName),
            subject: 'Invitación para colaborar en un curso - DigiMentor',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tutor_invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

