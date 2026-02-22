<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verificación de correo electrónico mediante OTP de 6 dígitos.
 *
 * POST /api/v1/auth/email/verify  → Verificar OTP y obtener token
 * POST /api/v1/auth/email/resend  → Reenviar código OTP
 */
class EmailVerificationController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/auth/email/verify
    // ──────────────────────────────────────────────────────────────

    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer'],
            'code'    => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User|null $user */
        $user = User::find($request->integer('user_id'));

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Código inválido o expirado.',
                'code'    => 'INVALID_OTP',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validar que el OTP exista, coincida y no haya expirado
        if (
            ! $user->email_otp ||
            ! $user->email_otp_expires_at ||
            $user->email_otp !== $request->string('code')->toString() ||
            now()->isAfter($user->email_otp_expires_at)
        ) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El código es incorrecto o ha expirado.',
                'code'    => 'INVALID_OTP',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Marcar email como verificado y limpiar el OTP
        $user->update([
            'email_verified_at'    => now(),
            'email_otp'            => null,
            'email_otp_expires_at' => null,
        ]);

        // Emitir token Sanctum
        $tokenName  = 'mobile_' . $user->id . '_' . now()->timestamp;
        $expiration = now()->addDays((int) config('sanctum.mobile_token_expiration', 30));

        $token = $user->createToken(
            name: $tokenName,
            abilities: $this->abilitiesForRole($user),
            expiresAt: $expiration,
        );

        ActivityLogger::log(
            logType: 'email_verified',
            description: "Email verificado: {$user->email}",
            user: $user,
            causer: $user,
            properties: ['ip' => $request->ip()],
        );

        return response()->json([
            'status'  => 'success',
            'message' => '¡Correo verificado exitosamente! Bienvenido a Bomberos App.',
            'data'    => [
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiration->toIso8601String(),
                'user'       => $this->userPayload($user),
            ],
        ], Response::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/auth/email/resend
    // ──────────────────────────────────────────────────────────────

    public function resend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User|null $user */
        $user = User::find($request->integer('user_id'));

        // Por seguridad, siempre responder igual aunque el usuario no exista
        if ($user && ! $user->email_verified_at) {
            OtpService::generate($user);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Si la cuenta existe y no está verificada, recibirás un nuevo código en tu correo.',
        ], Response::HTTP_OK);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers privados
    // ──────────────────────────────────────────────────────────────

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

        // aprendiz
        return [
            'mobile:read',
            'trainings:read',
            'reports:read',
            'profile:read',
            'profile:write',
        ];
    }

    private function userPayload(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'username'   => $user->username,
            'email'      => $user->email,
            'role'       => $user->getRoleNames()->first(),
            'avatar_url' => $user->avatar_url,
        ];
    }
}
