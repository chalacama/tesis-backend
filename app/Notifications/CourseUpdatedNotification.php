<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public ?User $actor = null, // quién hizo el cambio (tutor/admin)
    ) {}

    /**
     * Canales: solo base de datos por ahora.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
        // Más adelante podrías usar ['database', 'broadcast'] para tiempo real.
    }

    /**
     * Lo que se guarda en la columna "data" de la tabla notifications.
     * Esto es lo que tu Angular va a leer.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'key'          => 'course.updated',
            'title'        => 'Curso actualizado',
            'message'      => $this->buildMessage(),
            'course_id'    => $this->course->id,
            'course_title' => $this->course->title,
            'updated_at'   => now()->toIso8601String(),
            // Ajusta esta ruta a la que usas en Angular para ver el curso:
            'url'          => "/course/{$this->course->id}",
        ];
    }

    protected function buildMessage(): string
    {
        if ($this->actor) {
            $name = trim($this->actor->name . ' ' . ($this->actor->lastname ?? ''));
            $name = $name !== '' ? $name : $this->actor->username ?? 'un tutor';
            return sprintf('El curso "%s" ha sido actualizado por %s.', $this->course->title, $name);
        }

        return sprintf('El curso "%s" ha sido actualizado.', $this->course->title);
    }
}
