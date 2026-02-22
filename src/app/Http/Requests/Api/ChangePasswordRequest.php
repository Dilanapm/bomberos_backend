<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

/**
 * Validación para cambiar contraseña desde la app móvil.
 */
class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string'],
            'password'              => [
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'different:current_password',
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'  => 'La contraseña actual es obligatoria.',
            'password.required'          => 'La nueva contraseña es obligatoria.',
            'password.min'               => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'         => 'Las contraseñas no coinciden.',
            'password.regex'             => 'La contraseña debe contener mayúsculas, minúsculas y números.',
            'password.different'         => 'La nueva contraseña debe ser diferente a la actual.',
            'password_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
        ];
    }

    /**
     * Verifica que la contraseña actual sea correcta.
     * Se llama manualmente desde el controlador (no en rules para dar error específico).
     */
    public function verifyCurrentPassword(): bool
    {
        return Hash::check(
            $this->input('current_password'),
            $this->user()->password
        );
    }
}
