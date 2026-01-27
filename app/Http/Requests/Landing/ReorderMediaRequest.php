<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMediaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Obtiene las reglas de validación que aplican a la solicitud
     */
    public function rules(): array
    {
        return [
            'media_order' => 'required|array|min:1',
            'media_order.*.media_id' => 'required|exists:media,id',
            'media_order.*.sort_order' => 'required|integer|min:1',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'media_order.required' => 'El orden de media es obligatorio.',
            'media_order.array' => 'El orden de media debe ser un array.',
            'media_order.min' => 'Debe especificar al menos un media para reordenar.',
            'media_order.*.media_id.required' => 'El ID del media es obligatorio.',
            'media_order.*.media_id.exists' => 'Uno de los media especificados no existe.',
            'media_order.*.sort_order.required' => 'El orden es obligatorio para cada media.',
            'media_order.*.sort_order.integer' => 'El orden debe ser un número entero.',
            'media_order.*.sort_order.min' => 'El orden debe ser mayor a 0.',
        ];
    }
}