<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterAprendizRequest;
use App\Models\RegistrationCode;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registro de nuevos aprendices mediante código de instructor.
 *
 * POST /api/v1/auth/register
 */
class RegisterController extends Controller
{
    public function register(RegisterAprendizRequest $request): JsonResponse
    {
        $data = $request->validated();

        // 1. Buscar el código de registro válido
        /** @var RegistrationCode|null $code */
        $code = RegistrationCode::where('code', $data['registration_code'])
            ->valid()           // scope: no revocado, no expirado, no agotado
            ->lockForUpdate()   // evitar race condition si varios aprendices usan el mismo código
            ->first();

        if (! $code) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El código de registro es inválido o ha expirado.',
                'code'    => 'INVALID_REGISTRATION_CODE',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 2. Crear usuario y consumir código dentro de una transacción
        $user = DB::transaction(function () use ($data, $code) {
            $user = User::create([
                'name'     => $data['name'],
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                // email_verified_at se establece tras verificar el OTP enviado al correo
            ]);

            // Asignar rol aprendiz
            $user->assignRole('aprendiz');

            // Consumir un uso del código
            $code->registerUse();

            return $user;
        });

        // 3. Enviar OTP de verificación al correo del nuevo aprendiz
        OtpService::generate($user);

        // 4. Registrar en el log de actividad
        ActivityLogger::log(
            logType: 'aprendiz_registered',
            description: "Nuevo aprendiz registrado: {$user->email} (código: {$code->code})",
            user: $user,
            causer: $code->instructor,
            properties: [
                'instructor_id' => $code->instructor_id,
                'code'          => $code->code,
                'ip'            => $request->ip(),
            ],
        );

        // 5. Responder sin token — el aprendiz debe verificar su correo primero
        return response()->json([
            'status'  => 'success',
            'message' => '¡Registro exitoso! Hemos enviado un código de 6 dígitos a ' . $user->email . '. Ingrésalo para activar tu cuenta.',
            'data'    => [
                'user_id' => $user->id,
                'email'   => $user->email,
            ],
        ], Response::HTTP_CREATED);
    }
}
