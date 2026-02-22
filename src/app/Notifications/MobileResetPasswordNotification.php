<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación de restablecimiento de contraseña para la app móvil (Flutter).
 *
 * Sobreescribe la notificación base de Laravel para que el enlace
 * apunte a un deep link de Flutter en lugar de una URL web.
 *
 * Deep link format: bomberos://reset-password?token={token}&email={email}
 *
 * Configurable con APP_MOBILE_SCHEME en .env.docker
 */
class MobileResetPasswordNotification extends BaseResetPassword
{
    /**
     * Construye la URL que aparecerá en el correo.
     * Flutter intercepta este deep link y abre la pantalla de nueva contraseña.
     */
    protected function resetUrl(mixed $notifiable): string
    {
        $scheme = config('app.mobile_scheme', 'bomberos');
        $email  = urlencode($notifiable->getEmailForPasswordReset());
        $token  = urlencode($this->token);

        // El deep link que Flutter debe registrar en AndroidManifest.xml / Info.plist
        return "{$scheme}://reset-password?token={$token}&email={$email}";
    }

    /**
     * Contenido del correo electrónico.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Restablece tu contraseña — Bomberos App')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->line('Pulsa el botón desde tu dispositivo móvil para crear una nueva contraseña.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace expirará en **' . config('auth.passwords.users.expire', 60) . ' minutos**.')
            ->line('Si no solicitaste restablecer tu contraseña, ignora este correo. Tu cuenta está segura.')
            ->salutation('Equipo Bomberos App');
    }
}
