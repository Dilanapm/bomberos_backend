<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Form Request para login de la app móvil.
 *
 * Responsabilidades:
 *  - Sanitización y validación de inputs
 *  - Rate limiting por IP + email (5 intentos / 60 s)
 *  - Preparación de credenciales limpias
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ──────────────────────────────────────────────────
    //  Reglas de validación
    // ──────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'email.max'         => 'El correo electrónico no debe superar los 254 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'      => 'La contraseña no debe superar los 128 caracteres.',
        ];
    }

    // ──────────────────────────────────────────────────
    //  Sanitización de inputs antes de validar
    // ──────────────────────────────────────────────────

    protected function prepareForValidation(): void
    {
        // Eliminar espacios en el email y forzar minúsculas
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }
    }

    // ──────────────────────────────────────────────────
    //  Rate limiting: 5 intentos / 60 s por IP + email
    // ──────────────────────────────────────────────────

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]) ?: "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.",
        ]);
    }

    public function incrementRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), 60);
    }

    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Clave única por IP + email para aislar ataques de fuerza bruta.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('email')) . '|' . $this->ip()
        );
    }
}
