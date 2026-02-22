<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación con código OTP de 6 dígitos para verificar el correo
 * electrónico desde la aplicación móvil.
 *
 * El código expira en 10 minutos.
 */
class MobileEmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifica tu correo — Bomberos App')
            ->greeting('¡Hola, ' . $notifiable->name . '!')
            ->line('Ingresa el siguiente código en la aplicación para verificar tu correo electrónico:')
            ->line('')
            ->line('### ' . chunk_split($this->otp, 3, ' '))
            ->line('')
            ->line('Este código es válido durante **10 minutos**.')
            ->line('Si no solicitaste esto, puedes ignorar este mensaje.')
            ->salutation('Equipo Bomberos App');
    }
}
