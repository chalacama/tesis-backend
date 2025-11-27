<?php

namespace App\Notifications;

use App\Models\CourseInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TutorInvitationNotification extends Notification
{
    use Queueable;

    public CourseInvitation $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(CourseInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Solo guardamos en base de datos
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $course = $this->invitation->course;

        return [
            'type'       => 'course_invitation',
            'message'    => 'Has sido invitado a colaborar en el curso "' . ($course->title ?? '') . '".',
            'course_id'  => $this->invitation->course_id,
            'token'      => $this->invitation->token,
            'invitation_id' => $this->invitation->id,
            // Opcional: podrías guardar también la URL directa para aceptar:
            'accept_url' => url('/invitation/accept?token=' . $this->invitation->token),
        ];
    }
}
