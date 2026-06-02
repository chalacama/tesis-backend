<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOTPCode extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $code;
    public string $type;

    /**
     * Crea una nueva instancia del Mailable.
     *
     * @param User   $user El usuario destinatario
     * @param string $code El código OTP en texto plano (6 dígitos)
     * @param string $type El tipo de verificación ('email_verification', 'phone_verification', 'password_reset')
     */
    public function __construct(User $user, string $code, string $type)
    {
        $this->user = $user;
        $this->code = $code;
        $this->type = $type;
    }

    /**
     * Configura el sobre (envelope) del correo con el asunto dinámico.
     */
    public function envelope(): Envelope
    {
        // Asunto dinámico según el tipo de verificación
        $subjects = [
            'email_verification' => 'Código de verificación de correo electrónico - DigiMentor',
            'phone_verification' => 'Código de verificación de teléfono - DigiMentor',
            'password_reset'     => 'Código para restablecer tu contraseña - DigiMentor',
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'Código de verificación - DigiMentor',
        );
    }

    /**
     * Define el contenido del correo, apuntando a la vista Blade.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp_code',
        );
    }

    /**
     * Archivos adjuntos (ninguno en este caso).
     */
    public function attachments(): array
    {
        return [];
    }
}
