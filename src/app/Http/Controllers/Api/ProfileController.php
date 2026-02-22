<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestión de perfil del usuario autenticado (instructor / aprendiz).
 *
 * PATCH  /api/v1/profile            → Actualizar nombre y/o username
 * POST   /api/v1/profile/password   → Cambiar contraseña
 * POST   /api/v1/profile/avatar     → Subir foto de perfil
 * DELETE /api/v1/profile/avatar     → Eliminar foto de perfil
 */
class ProfileController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  PATCH /api/v1/profile
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        $user->fill($data)->save();

        ActivityLogger::log(
            logType: 'profile_updated',
            description: "Perfil actualizado: {$user->email}",
            user: $user,
            causer: $user,
            properties: ['changed_fields' => array_keys($data)],
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Perfil actualizado correctamente.',
            'data'    => $this->profilePayload($user),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/profile/password
    // ──────────────────────────────────────────────────────────────

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Verificar que la contraseña actual sea correcta
        if (! $request->verifyCurrentPassword()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La contraseña actual es incorrecta.',
                'code'    => 'INVALID_CURRENT_PASSWORD',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        // Revocar todos los tokens excepto el actual
        // El usuario sigue con su sesión activa en este dispositivo
        $currentToken = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentToken)->delete();

        ActivityLogger::log(
            logType: 'password_changed',
            description: "Contraseña cambiada desde el perfil: {$user->email}",
            user: $user,
            causer: $user,
            properties: ['ip' => $request->ip()],
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/profile/avatar
    // ──────────────────────────────────────────────────────────────

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',          // 2 MB máximo
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
        ], [
            'avatar.required'    => 'La imagen es obligatoria.',
            'avatar.image'       => 'El archivo debe ser una imagen.',
            'avatar.mimes'       => 'Solo se permiten imágenes JPG, PNG o WEBP.',
            'avatar.max'         => 'La imagen no debe superar los 2 MB.',
            'avatar.dimensions'  => 'La imagen debe tener entre 100x100 y 2000x2000 píxeles.',
        ]);

        /** @var User $user */
        $user = $request->user();

        // Eliminar avatar anterior si existe
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Guardar nuevo avatar en storage/app/public/avatars/{user_id}/
        $path = $request->file('avatar')->store(
            "avatars/{$user->id}",
            'public'
        );

        $user->update(['avatar' => $path]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Foto de perfil actualizada.',
            'data'      => [
                'avatar_url' => $user->fresh()->avatar_url,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  DELETE /api/v1/profile/avatar
    // ──────────────────────────────────────────────────────────────

    public function deleteAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Foto de perfil eliminada.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helper
    // ──────────────────────────────────────────────────────────────

    private function profilePayload(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'username'   => $user->username,
            'email'      => $user->email,
            'avatar_url' => $user->avatar_url,
            'role'       => $user->getRoleNames()->first(),
        ];
    }
}
