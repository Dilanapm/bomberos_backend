<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Validación para restablecer la contraseña con el token recibido por email.
 */
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'string', 'email:rfc', 'max:254'],
            'password'              => [
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',                     // requiere campo password_confirmation
                'regex:/[a-z]/',                 // al menos una minúscula
                'regex:/[A-Z]/',                 // al menos una mayúscula
                'regex:/[0-9]/',                 // al menos un número
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'                 => 'El token de restablecimiento es obligatorio.',
            'email.required'                 => 'El correo electrónico es obligatorio.',
            'email.email'                    => 'El correo electrónico no tiene un formato válido.',
            'password.required'              => 'La nueva contraseña es obligatoria.',
            'password.min'                   => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'                   => 'La contraseña no debe superar los 128 caracteres.',
            'password.confirmed'             => 'Las contraseñas no coinciden.',
            'password.regex'                 => 'La contraseña debe contener mayúsculas, minúsculas y números.',
            'password_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }
    }
}
