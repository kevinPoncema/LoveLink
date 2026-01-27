<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandingRequest extends FormRequest
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
            'couple_names' => 'required|string|max:200',
            'slug' => 'nullable|string|max:50|regex:/^[a-z0-9\-]+$/',
            'anniversary_date' => 'required|date',
            'theme_id' => 'required|exists:themes,id',
            'bio_text' => 'nullable|string',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'couple_names.required' => 'Los nombres de la pareja son obligatorios.',
            'couple_names.max' => 'Los nombres de la pareja no pueden exceder 200 caracteres.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'slug.max' => 'El slug no puede exceder 50 caracteres.',
            'anniversary_date.required' => 'La fecha de aniversario es obligatoria.',
            'anniversary_date.date' => 'La fecha de aniversario debe ser una fecha válida.',
            'theme_id.required' => 'El tema es obligatorio.',
            'theme_id.exists' => 'El tema seleccionado no existe.',
        ];
    }
}