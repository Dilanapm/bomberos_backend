<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\User;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se gestiona vía middleware (auth:sanctum + role)
    }

    public function rules(): array
    {
        return [
            // ── Aprendiz objetivo (solo instructores) ────────────────────
            'user_id'                => ['nullable', 'integer', 'exists:users,id'],

            // ── Datos generales ──────────────────────────────────────────
            'session_id'             => ['required', 'string', 'max:100'],
            'general_score'          => ['required', 'numeric', 'min:0', 'max:100'],
            'total_steps'            => ['required', 'integer', 'min:1', 'max:10'],
            'steps_completed'        => ['required', 'integer', 'min:0', 'max:10'],
            'correct_order'          => ['required', 'boolean'],

            // ── Estadísticas del video ───────────────────────────────────
            'duration_seconds'       => ['required', 'numeric', 'min:0'],
            'total_frames'           => ['required', 'integer', 'min:0'],
            'frames_with_detection'  => ['required', 'integer', 'min:0'],
            'detection_rate'         => ['required', 'numeric', 'min:0', 'max:100'],

            // ── Recomendaciones (array o string) ────────────────────────
            'recommendations'        => ['nullable'],

            // ── Pasos (exactamente 6) ────────────────────────────────────
            'steps_evaluation'                  => ['required', 'array', 'size:6'],
            'steps_evaluation.*.step_number'    => ['required', 'integer', 'min:1', 'max:6'],
            'steps_evaluation.*.step_name'      => ['required', 'string', 'max:100'],
            'steps_evaluation.*.score'          => ['required', 'numeric', 'min:0', 'max:1'],
            'steps_evaluation.*.status'         => ['required', 'in:correcto,incorrecto'],
            'steps_evaluation.*.detected'       => ['required', 'boolean'],
            'steps_evaluation.*.feedback'       => ['required', 'string'],
            'steps_evaluation.*.time_start'     => ['nullable', 'numeric', 'min:0'],
            'steps_evaluation.*.time_end'       => ['nullable', 'numeric', 'min:0'],
            'steps_evaluation.*.duration'       => ['nullable', 'numeric', 'min:0'],

            // ── Errores (opcional) ───────────────────────────────────────
            'errors'                  => ['nullable', 'array'],
            'errors.*.step_number'    => ['nullable', 'integer', 'min:1', 'max:6'],
            'errors.*.error_type'     => ['required_with:errors', 'in:paso_omitido,orden_incorrecto,mala_ejecucion,tiempo_insuficiente,deteccion_fallida'],
            'errors.*.description'    => ['required_with:errors', 'string'],
            'errors.*.severity'       => ['required_with:errors', 'in:baja,media,alta'],

            // ── Timeline (opcional) ──────────────────────────────────────
            'timeline'                => ['nullable', 'array'],
            'timeline.*.time'         => ['required_with:timeline', 'numeric', 'min:0'],
            'timeline.*.step_detected' => ['required_with:timeline', 'integer', 'min:0'],
            'timeline.*.confidence'   => ['required_with:timeline', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists'               => 'El aprendiz especificado no existe.',
            'session_id.required'          => 'El ID de sesión es obligatorio.',
            'general_score.required'       => 'El puntaje general es obligatorio.',
            'general_score.between'        => 'El puntaje debe estar entre 0 y 100.',
            'steps_evaluation.required'    => 'La evaluación de pasos es obligatoria.',
            'steps_evaluation.size'        => 'Se requieren exactamente 6 pasos de evaluación.',
            'steps_evaluation.*.status.in' => 'El estado del paso debe ser "correcto" o "incorrecto".',
            'errors.*.error_type.in'       => 'El tipo de error no es válido.',
            'errors.*.severity.in'         => 'La severidad debe ser "baja", "media" o "alta".',
        ];
    }

    /**
     * Normaliza recommendations: si llega como array, lo convierte a string.
     */
    protected function prepareForValidation(): void
    {
        if (is_array($this->recommendations)) {
            $this->merge([
                'recommendations' => implode("\n", $this->recommendations),
            ]);
        }
    }

    /**
     * Validación extra: el user_id enviado debe pertenecer a un aprendiz.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $userId = $this->input('user_id');

            if ($userId && ! $v->errors()->has('user_id')) {
                $user = User::find($userId);

                if ($user && ! $user->hasRole('aprendiz')) {
                    $v->errors()->add(
                        'user_id',
                        'El usuario indicado no tiene rol aprendiz. Las evaluaciones EPP solo pueden registrarse para aprendices.'
                    );
                }
            }
        });
    }
}
