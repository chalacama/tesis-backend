<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CertificateObtainedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Certificate $certificate,
        public Course $course,
    ) {}

    /**
     * Canales por los que se envía la notificación.
     * De momento SOLO database (para el frontend).
     */
    public function via(object $notifiable): array
    {
        return ['database'];
        // Más adelante, si quieres también correo:
        // return ['database', 'mail'];
    }

    /**
     * Si más adelante activas 'mail', aquí defines el correo.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->subject('Has obtenido tu certificado 🎓')
    //         ->line("Has completado el curso {$this->course->title}.")
    //         ->action('Ver certificado', url("/certificate/{$this->certificate->code}"));
    // }

    /**
     * Lo que se guarda en la columna 'data' de la tabla notifications.
     * Esto es lo que consumirás desde Angular.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'key'              => 'certificate.obtained',
            'title'            => '🎓 ¡Has obtenido un certificado!',
            'message'          => "Has completado el curso {$this->course->title}.",
            'certificate_code' => $this->certificate->code,
            'course_id'        => $this->course->id,
            'course_title'     => $this->course->title,
            'issued_at'        => optional($this->certificate->created_at)->toIso8601String(),
            // Ruta que luego usarás en Angular: /certificate/:code
            'url'              => "/certificate/{$this->certificate->code}",
        ];
    }
}
