<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso a la API móvil para roles no permitidos.
 *
 * Roles permitidos: instructor, aprendiz
 * Roles bloqueados: admin (usa el panel web, no la app)
 */
class EnsureMobileRoleAccess
{
    private const ALLOWED_ROLES = ['instructor', 'aprendiz'];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Seguridad defensiva: nunca debería llegar sin autenticación,
        // pero lo verificamos por si el middleware se usa mal.
        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No autenticado.',
                'code'    => 'UNAUTHENTICATED',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Cuenta deshabilitada
        if ($user->isDisabled()) {
            // Revocar todos los tokens activos del usuario
            $user->tokens()->delete();

            return response()->json([
                'status'  => 'error',
                'message' => 'Cuenta desactivada por el administrador. Contáctese con el área correspondiente para activar su cuenta.',
                'code'    => 'ACCOUNT_DISABLED',
            ], Response::HTTP_FORBIDDEN);
        }

        // Email no verificado (safety-net: normalmente no tienen token hasta verificar)
        if (! $user->email_verified_at) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tu correo electrónico no ha sido verificado.',
                'code'    => 'EMAIL_NOT_VERIFIED',
                'data'    => [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Solo roles permitidos pueden usar la app móvil
        if (! $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tienes permiso para acceder a la aplicación móvil.',
                'code'    => 'ROLE_FORBIDDEN',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
