<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para editar perfil (nombre, username).
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'     => [
                'sometimes',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\s\-\.]+$/u'
            ],   // solo letras, espacios, guiones, puntos
            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_\.]+$/', // letras, números, _ y .
                Rule::unique('users', 'username')->ignore($userId)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min'          => 'El nombre debe tener al menos 2 caracteres.',
            'name.max'          => 'El nombre no debe superar los 100 caracteres.',
            'name.regex'        => 'El nombre solo puede contener letras, espacios, guiones y puntos.',
            'username.min'      => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'username.max'      => 'El nombre de usuario no debe superar los 50 caracteres.',
            'username.regex'    => 'El nombre de usuario solo puede contener letras, números, guiones bajos y puntos.',
            'username.unique'   => 'Este nombre de usuario ya está en uso.',
        ];
    }
}
