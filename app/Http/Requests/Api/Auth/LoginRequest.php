<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\Shared\BaseRequest;
use App\Rules\Energy4CeroEmail;

class LoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new Energy4CeroEmail],
            'password' => 'required|string|min:6',
            'remember_me' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'remember_me.boolean' => 'El campo recordar sesión debe ser verdadero o falso.',
        ]);
    }
}

