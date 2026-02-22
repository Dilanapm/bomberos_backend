<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\RegistrationCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Validación para el registro de un aprendiz mediante código de instructor.
 */
class RegisterAprendizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\s\-\.]+$/u'
            ],
            'username'              => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_\.]+$/',
                'unique:users,username'
            ],
            'email'                 => [
                'required',
                'string',
                'email:rfc',
                'max:254',
                'unique:users,email'
            ],
            'password'              => [
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/'
            ],
            'password_confirmation' => ['required', 'string'],
            'registration_code'     => ['required', 'string', 'size:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'El nombre completo es obligatorio.',
            'name.regex'                => 'El nombre solo puede contener letras, espacios, guiones y puntos.',
            'username.required'         => 'El nombre de usuario es obligatorio.',
            'username.unique'           => 'Este nombre de usuario ya está en uso.',
            'username.regex'            => 'El nombre de usuario solo puede contener letras, números, _ y puntos.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.unique'              => 'Este correo electrónico ya está registrado.',
            'password.required'         => 'La contraseña es obligatoria.',
            'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'        => 'Las contraseñas no coinciden.',
            'password.regex'            => 'La contraseña debe contener mayúsculas, minúsculas y números.',
            'registration_code.required' => 'El código de registro es obligatorio.',
            'registration_code.size'    => 'El código de registro debe tener exactamente 8 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'             => Str::lower(trim((string) $this->input('email', ''))),
            'username'          => Str::lower(trim((string) $this->input('username', ''))),
            'registration_code' => Str::upper(trim((string) $this->input('registration_code', ''))),
        ]);
    }
}
