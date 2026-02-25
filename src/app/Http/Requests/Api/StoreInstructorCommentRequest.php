<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstructorCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step_number' => ['nullable', 'integer', 'min:1', 'max:6'],
            'comment'     => ['required', 'string', 'min:5', 'max:1000'],
            'type'        => ['required', 'in:correcion,felicitacion,observacion'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'El comentario es obligatorio.',
            'comment.min'      => 'El comentario debe tener al menos 5 caracteres.',
            'type.required'    => 'El tipo de comentario es obligatorio.',
            'type.in'          => 'El tipo debe ser "correcion", "felicitacion" u "observacion".',
            'step_number.min'  => 'El número de paso debe ser entre 1 y 6.',
            'step_number.max'  => 'El número de paso debe ser entre 1 y 6.',
        ];
    }
}
