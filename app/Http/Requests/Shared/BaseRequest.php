<?php

namespace App\Http\Requests\Shared;

use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Override in child classes for specific authorization logic
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get common validation rules that apply to all requests
     * Override in child classes for specific rules
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get common error messages
     * Override in child classes for specific messages
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'unique' => 'El :attribute ya está en uso.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no debe superar los :max caracteres.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'string' => 'El campo :attribute debe ser una cadena de texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser un número.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'image' => 'El archivo debe ser una imagen.',
            'mimes' => 'El archivo debe ser de tipo: :values.',
            'file' => 'El campo :attribute debe ser un archivo.',
        ];
    }

    /**
     * Get custom attribute names for validator errors
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'phone' => 'teléfono',
            'username' => 'nombre de usuario',
            'job_title' => 'cargo de trabajo',
            'profile_photo' => 'foto de perfil',
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'birth_date' => 'fecha de nacimiento',
            'gender' => 'género',
            'address' => 'dirección',
        ];
    }

    /**
     * Prepare the data for validation
     * Override in child classes for specific data preparation
     */
    protected function prepareForValidation(): void
    {
        // Common data preparation logic can be added here
    }

    /**
     * Get the validation rules that apply to the request after data preparation
     * Override in child classes for specific rules
     */
    public function withValidator($validator): void
    {
        // Common validation logic can be added here
    }
}

