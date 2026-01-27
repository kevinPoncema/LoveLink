<?php

namespace App\Http\Requests\Themes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'primary_color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bg_color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bg_image_file' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // 10MB
            'bg_image_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'css_class' => ['sometimes', 'string', 'max:100'],
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
            'name.max' => 'El nombre del tema no puede tener más de 100 caracteres.',
            'primary_color.regex' => 'El color primario debe tener formato hex válido (#RRGGBB).',
            'secondary_color.regex' => 'El color secundario debe tener formato hex válido (#RRGGBB).',
            'bg_color.regex' => 'El color de fondo debe tener formato hex válido (#RRGGBB).',
            'bg_image_file.image' => 'El archivo de imagen de fondo debe ser una imagen válida.',
            'bg_image_file.mimes' => 'La imagen de fondo debe ser de tipo: jpg, jpeg, png, webp.',
            'bg_image_file.max' => 'La imagen de fondo no puede ser mayor a 10MB.',
            'bg_image_url.url' => 'La URL de la imagen de fondo debe ser válida.',
            'bg_image_url.max' => 'La URL de la imagen de fondo no puede tener más de 500 caracteres.',
            'css_class.max' => 'La clase CSS no puede tener más de 100 caracteres.',
        ];
    }
}