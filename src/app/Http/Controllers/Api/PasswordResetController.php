<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\MobileResetPasswordNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestión de restablecimiento de contraseña para la app móvil (Flutter).
 *
 * PASO 1 — Flutter llama:
 *   POST /api/v1/auth/forgot-password  { email }
 *   → Backend envía correo con deep link: bomberos://reset-password?token=xxx&email=xxx
 *
 * PASO 2 — Flutter abre el deep link, muestra formulario y llama:
 *   POST /api/v1/auth/reset-password   { token, email, password, password_confirmation }
 *   → Backend valida token, cambia contraseña y revoca todos los tokens Sanctum activos
 */
class PasswordResetController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  PASO 1 — POST /api/v1/auth/forgot-password
    // ──────────────────────────────────────────────────────────────

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        // Buscar usuario y solo enviar si existe Y tiene rol móvil válido.
        // Pero SIEMPRE devolver el mismo mensaje (anti user-enumeration).
        $user = User::where('email', $email)->first();

        if ($user && $user->hasAnyRole(['instructor', 'aprendiz']) && $user->isActive()) {
            // Usar el broker de passwords de Laravel para generar y guardar el token
            Password::broker()->sendResetLink(
                ['email' => $email],
                function (User $user, string $token) {
                    // Enviar nuestra notificación personalizada con deep link Flutter
                    $user->notify(new MobileResetPasswordNotification($token));
                }
            );

            ActivityLogger::log(
                logType: 'mobile_password_reset_requested',
                description: "Solicitud de reset de contraseña móvil: {$email}",
                user: $user,
                causer: $user,
                properties: [
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            );
        }

        // SIEMPRE la misma respuesta — nunca revelar si el email existe
        return response()->json([
            'status'  => 'success',
            'message' => 'Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.',
            'code'    => 'RESET_LINK_SENT',
        ], Response::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────
    //  PASO 2 — POST /api/v1/auth/reset-password
    // ──────────────────────────────────────────────────────────────

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Actualizar contraseña
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // Revocar todos los tokens Sanctum activos en todos los dispositivos
                // por seguridad: si alguien robó el token anterior, ya no sirve.
                $user->tokens()->delete();

                ActivityLogger::log(
                    logType: 'mobile_password_reset_completed',
                    description: "Contraseña restablecida (móvil): {$user->email}",
                    user: $user,
                    causer: $user,
                    properties: ['ip' => request()->ip()],
                );
            }
        );

        // El broker devuelve una clave de traducción: PASSWORD_RESET o PASSWORD_INVALID, etc.
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Contraseña restablecida correctamente. Inicia sesión con tu nueva contraseña.',
                'code'    => 'PASSWORD_RESET_SUCCESS',
            ], Response::HTTP_OK);
        }

        // Token inválido, expirado o email no encontrado
        $errorMessages = [
            Password::INVALID_TOKEN   => 'El enlace de restablecimiento es inválido o ha expirado.',
            Password::INVALID_USER    => 'No encontramos una cuenta con ese correo electrónico.',
            Password::RESET_THROTTLED => 'Demasiados intentos. Inténtalo de nuevo más tarde.',
        ];

        return response()->json([
            'status'  => 'error',
            'message' => $errorMessages[$status] ?? 'No se pudo restablecer la contraseña.',
            'code'    => 'PASSWORD_RESET_FAILED',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
