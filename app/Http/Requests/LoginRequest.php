<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'El correo electrónico o nombre de usuario es requerido.',
            'identifier.string' => 'El identificador debe ser una cadena de texto.',
            'identifier.max' => 'El identificador no puede tener más de 255 caracteres.',
            'password.required' => 'La contraseña es requerida.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 1 caracter.',
        ];
    }

    /**
     * Get the identifier (email or username) from the request.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string) $this->identifier;
    }

    /**
     * Get the identifier type (email or username).
     *
     * @return string
     */
    public function getIdentifierType(): string
    {
        return filter_var($this->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }
}
