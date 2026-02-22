<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegistrationCode;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestión de códigos de registro para Instructores.
 *
 * POST   /api/v1/instructor/registration-code          → Generar código (30 min)
 * GET    /api/v1/instructor/registration-code/active   → Ver código activo actual
 * DELETE /api/v1/instructor/registration-code          → Revocar código activo
 *
 * Solo accesible por usuarios con rol 'instructor'.
 */
class InstructorCodeController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/instructor/registration-code
    // ──────────────────────────────────────────────────────────────

    public function generate(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Revocar cualquier código activo previo del mismo instructor
        // (un instructor solo debería tener un código activo a la vez)
        RegistrationCode::where('instructor_id', $user->id)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->update(['revoked' => true]);

        // Generar código único de 8 caracteres en mayúsculas
        do {
            $code = Str::upper(Str::random(8));
        } while (RegistrationCode::where('code', $code)->exists());

        $registrationCode = RegistrationCode::create([
            'code'          => $code,
            'instructor_id' => $user->id,
            'expires_at'    => now()->addMinutes(30),
            'max_uses'      => 50,      // hasta 50 aprendices durante los 30 minutos
            'uses'          => 0,
            'revoked'       => false,
        ]);

        ActivityLogger::log(
            logType: 'registration_code_generated',
            description: "Código de registro generado por instructor: {$user->email}",
            user: $user,
            causer: $user,
            properties: [
                'code'       => $code,
                'expires_at' => $registrationCode->expires_at->toIso8601String(),
                'ip'         => $request->ip(),
            ],
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Código generado. Compártelo con el aprendiz. Expira en 30 minutos.',
            'data'    => [
                'code'           => $registrationCode->code,
                'expires_at'     => $registrationCode->expires_at->toIso8601String(),
                'expires_in_min' => 30,
            ],
        ], Response::HTTP_CREATED);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/registration-code/active
    // ──────────────────────────────────────────────────────────────

    public function active(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $code = RegistrationCode::where('instructor_id', $user->id)
            ->valid()
            ->latest()
            ->first();

        if (! $code) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No tienes un código activo.',
                'data'    => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'code'           => $code->code,
                'expires_at'     => $code->expires_at->toIso8601String(),
                'expires_in_sec' => now()->diffInSeconds($code->expires_at),
                'uses'           => $code->uses,
                'max_uses'       => $code->max_uses,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  DELETE /api/v1/instructor/registration-code
    // ──────────────────────────────────────────────────────────────

    public function revoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $revoked = RegistrationCode::where('instructor_id', $user->id)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->update(['revoked' => true]);

        if (! $revoked) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No había ningún código activo para revocar.',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Código revocado correctamente.',
        ]);
    }
}
