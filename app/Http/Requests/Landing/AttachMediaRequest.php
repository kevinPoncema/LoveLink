<?php

namespace App\Http\Requests\Landing;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class AttachMediaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud
     */
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $mediaId = $this->input('media_id');
        if ($mediaId) {
            $media = Media::find($mediaId);
            if ($media && $media->user_id !== auth()->id()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene las reglas de validación que aplican a la solicitud
     */
    public function rules(): array
    {
        return [
            'media_id' => 'required|exists:media,id',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'media_id.required' => 'El ID del media es obligatorio.',
            'media_id.exists' => 'El media seleccionado no existe.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
            'sort_order.min' => 'El orden debe ser mayor o igual a 0.',
        ];
    }
}
