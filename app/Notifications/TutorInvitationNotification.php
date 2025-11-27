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
    $frontendUrl = config('app.frontend_url', config('app.url'));
    $acceptUrl = rtrim($frontendUrl, '/') . '/invitation/accept?token=' . $this->invitation->token;

    return [
        'key'          => 'course.invitation', // encaja con NotificationKey | string
        'title'        => 'Invitación a colaborar en un curso',
        'message'      => 'Has sido invitado a colaborar en el curso "' . ($course->title ?? '') . '".',
        'url'          => $acceptUrl, // 🔗 ruta pública de Angular

        'course_id'    => $this->invitation->course_id,
        'course_title' => $course->title ?? null,
        'token'        => $this->invitation->token,
        'invitation_id'=> $this->invitation->id,
    ];
}

}
