<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\MobileEmailVerificationNotification;

/**
 * Servicio para generar y enviar códigos OTP de verificación de email.
 */
class OtpService
{
    /**
     * Genera un OTP de 6 dígitos, lo guarda en el usuario y envía el correo.
     *
     * Siempre sobreescribe el OTP anterior para que el último código enviado
     * sea el único válido.
     */
    public static function generate(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->email_otp            = $code;
        $user->email_otp_expires_at = now()->addMinutes(10);
        $user->save();

        $user->notify(new MobileEmailVerificationNotification($code));
    }
}
