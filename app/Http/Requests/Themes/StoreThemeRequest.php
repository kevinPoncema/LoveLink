<?php

namespace App\Http\Requests\Themes;

use Illuminate\Foundation\Http\FormRequest;

class StoreThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bg_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bg_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // 10MB
            'bg_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'bg_image_url' => ['nullable', 'url', 'max:500'],
            'css_class' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del tema es requerido.',
            'name.max' => 'El nombre del tema no puede tener más de 100 caracteres.',
            'primary_color.required' => 'El color primario es requerido.',
            'primary_color.regex' => 'El color primario debe tener formato hex válido (#RRGGBB).',
            'secondary_color.required' => 'El color secundario es requerido.',
            'secondary_color.regex' => 'El color secundario debe tener formato hex válido (#RRGGBB).',
            'bg_color.required' => 'El color de fondo es requerido.',
            'bg_color.regex' => 'El color de fondo debe tener formato hex válido (#RRGGBB).',
            'bg_image_file.image' => 'El archivo de imagen de fondo debe ser una imagen válida.',
            'bg_image_file.mimes' => 'La imagen de fondo debe ser de tipo: jpg, jpeg, png, webp.',
            'bg_image_file.max' => 'La imagen de fondo no puede ser mayor a 10MB.',
            'bg_image_url.url' => 'La URL de la imagen de fondo debe ser válida.',
            'bg_image_url.max' => 'La URL de la imagen de fondo no puede tener más de 500 caracteres.',
            'css_class.required' => 'La clase CSS es requerida.',
            'css_class.max' => 'La clase CSS no puede tener más de 100 caracteres.',
        ];
    }
}
