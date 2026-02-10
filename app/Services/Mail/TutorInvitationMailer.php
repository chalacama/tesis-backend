<?php

namespace App\Services\Mail;

use App\Mail\TutorInvitationEmail;
use App\Models\CourseInvitation;
use Illuminate\Support\Facades\Mail;

class TutorInvitationMailer
{
    public function __construct(
        private readonly RecipientMailProviderResolver $resolver
    ) {}

    public function send(CourseInvitation $invitation): void
    {
        $email = $invitation->email;
        $provider = $this->resolver->resolve($email);

        if ($provider === 'gmail') {
            $this->sendViaGmail($invitation);
            return;
        }

        if ($provider === 'outlook') {
            $this->sendViaOutlook($invitation);
            return;
        }

        // ✅ Otros dominios: envía a los 2
        $this->sendViaGmail($invitation);
        $this->sendViaOutlook($invitation);
    }

    private function sendViaGmail(CourseInvitation $invitation): void
    {
        Mail::mailer('gmail')
            ->to($invitation->email)
            ->send(new TutorInvitationEmail($invitation));
    }

    private function sendViaOutlook(CourseInvitation $invitation): void
    {
        $mailable = (new TutorInvitationEmail($invitation))
            ->from(
                config('mail.outlook_from.address'),
                config('mail.outlook_from.name')
            );

        Mail::mailer('outlook')
            ->to($invitation->email)
            ->send($mailable);
    }
}