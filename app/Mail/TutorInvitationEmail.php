<?php

namespace App\Mail;

use App\Models\CourseInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorInvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public CourseInvitation $invitation;
    public string $acceptUrl; // 🔹 nueva propiedad

    public function __construct(CourseInvitation $invitation)
    {
        $this->invitation = $invitation;

        $frontendUrl = config('app.frontend_url', config('app.url'));
        $this->acceptUrl = rtrim($frontendUrl, '/') . '/invitation/accept?token=' . $invitation->token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitación para colaborar en un curso',
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

