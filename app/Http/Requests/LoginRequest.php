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
            'email' => 'required_without:username|email|max:255',
            'username' => 'required_without:email|string|max:255',
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
            'email.required_without' => 'El correo electrónico es requerido cuando no se proporciona nombre de usuario.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'username.required_without' => 'El nombre de usuario es requerido cuando no se proporciona correo electrónico.',
            'username.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'username.max' => 'El nombre de usuario no puede tener más de 255 caracteres.',
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
        return $this->filled('email') ? $this->email : $this->username;
    }

    /**
     * Get the identifier type (email or username).
     *
     * @return string
     */
    public function getIdentifierType(): string
    {
        return $this->filled('email') ? 'email' : 'username';
    }
}
