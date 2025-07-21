<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends VerifyEmailBase
{
    public function toMail($notifiable)
    {
        // Get the redirect route from config and combine with FRONTEND_URL
        $redirectUrl = config('frontend.url') . config('frontend.routes.home');

        // Generate the signed verification URL with a redirect parameter
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'redirect' => $redirectUrl,
            ]
        );

        return (new MailMessage)
            ->subject('Verifica tu correo electrónico')
            ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo.')
            ->action('Verificar Correo', $verificationUrl)
            ->line('Si no creaste una cuenta, no es necesario que hagas nada.');
    }
}