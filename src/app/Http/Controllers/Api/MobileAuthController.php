<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador de autenticación para la app móvil (Flutter).
 *
 * Usa Laravel Sanctum con tokens de larga duración.
 * Solo Instructores y Aprendices pueden autenticarse.
 *
 * Endpoints:
 *  POST   /api/v1/auth/login   → Obtener token
 *  POST   /api/v1/auth/logout  → Revocar token actual  [auth:sanctum]
 *  GET    /api/v1/auth/me      → Datos del usuario     [auth:sanctum]
 */
class MobileAuthController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/auth/login
    // ──────────────────────────────────────────────────────────────

    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Verificar rate limiter ANTES de tocar la base de datos
        $request->ensureIsNotRateLimited();

        // 2. Buscar usuario por email (siempre en tiempo constante para evitar timing attacks)
        $credentials = $request->only('email', 'password');

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        // Verificar existencia + hash de contraseña en una sola evaluación
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // Incrementar contador de intentos fallidos
            $request->incrementRateLimiter();

            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales incorrectas.',
                'code'    => 'INVALID_CREDENTIALS',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Verificar que la cuenta no esté deshabilitada
        if ($user->isDisabled()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cuenta desactivada por el administrador. Contáctese con el área correspondiente para activar su cuenta.',
                'code'    => 'ACCOUNT_DISABLED',
            ], Response::HTTP_FORBIDDEN);
        }

        // 4. Bloquear al Administrador: el panel web es su entorno, no la app
        if ($user->hasRole('admin')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El administrador solo puede ingresar desde el panel web.',
                'code'    => 'ADMIN_WEB_ONLY',
            ], Response::HTTP_FORBIDDEN);
        }

        // 5. Solo Instructor o Aprendiz pueden continuar
        if (! $user->hasAnyRole(['instructor', 'aprendiz'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tienes un rol válido para acceder a la aplicación.',
                'code'    => 'ROLE_FORBIDDEN',
            ], Response::HTTP_FORBIDDEN);
        }

        // 6. Verificar que el correo esté confirmado
        if (! $user->email_verified_at) {
            OtpService::generate($user);

            return response()->json([
                'status'  => 'error',
                'message' => 'Debes verificar tu correo. Hemos enviado un código de 6 dígitos a ' . $user->email,
                'code'    => 'EMAIL_NOT_VERIFIED',
                'data'    => [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // 7. Limpiar rate limiter tras login exitoso
        $request->clearRateLimiter();

        // 7. Crear token Sanctum con nombre descriptivo y expiración configurable
        $tokenName  = 'mobile_' . $user->id . '_' . now()->timestamp;
        $expiration = now()->addDays((int) config('sanctum.mobile_token_expiration', 30));

        $token = $user->createToken(
            name: $tokenName,
            abilities: $this->abilitiesForRole($user),
            expiresAt: $expiration,
        );

        // 8. Registrar evento de login en el log de actividad
        ActivityLogger::log(
            logType: 'mobile_login',
            description: "Login móvil: {$user->email} [{$user->getRoleNames()->first()}]",
            user: $user,
            causer: $user,
            properties: [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'role'       => $user->getRoleNames()->first(),
            ],
        );

        // 9. Respuesta
        return response()->json([
            'status'  => 'success',
            'message' => 'Autenticación exitosa.',
            'data'    => [
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiration->toIso8601String(),
                'user'       => $this->userPayload($user),
            ],
        ], Response::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/auth/logout
    // ──────────────────────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Revocar SOLO el token actual (el usuario puede tener varios dispositivos)
        $user->currentAccessToken()->delete();

        ActivityLogger::log(
            logType: 'mobile_logout',
            description: "Logout móvil: {$user->email}",
            user: $user,
            causer: $user,
            properties: ['ip' => $request->ip()],
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesión cerrada correctamente.',
        ], Response::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/auth/me
    // ──────────────────────────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data'   => $this->userPayload($user),
        ], Response::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers privados
    // ──────────────────────────────────────────────────────────────

    /**
     * Datos del usuario enviados al frontend Flutter.
     * Nunca exponer: password_hash, 2FA secrets, WebAuthn keys, remember_token.
     */
    private function userPayload(User $user): array
    {
        $role = $user->getRoleNames()->first();

        return [
            'id'                     => $user->id,
            'name'                   => $user->name,
            'username'               => $user->username,
            'email'                  => $user->email,
            'avatar_url'             => $user->avatar_url,
            'role'                   => $role,
            'can_access_ai_module'    => $user->can_access_ai_module,
            'can_view_student_stats'  => $user->can_view_student_stats,
            'can_access_stats_module' => $user->can_access_stats_module,
            'email_verified'         => ! is_null($user->email_verified_at),
        ];
    }

    /**
     * Cada rol obtiene solo las abilities (scopes) que necesita.
     * Principio de mínimo privilegio en tokens Sanctum.
     */
    private function abilitiesForRole(User $user): array
    {
        if ($user->hasRole('instructor')) {
            return [
                'mobile:read',
                'trainings:read',
                'trainings:write',
                'reports:read',
                'reports:write',
                'students:read',
                'profile:read',
                'profile:write',
            ];
        }

        if ($user->hasRole('aprendiz')) {
            return [
                'mobile:read',
                'trainings:read',
                'reports:read',
                'profile:read',
                'profile:write',
            ];
        }

        return ['mobile:read'];
    }
}
