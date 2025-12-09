<?php

namespace App\Http\Requests\Apk\Auth;

use App\Http\Requests\Shared\BaseRequest;
use App\Rules\Energy4CeroEmail;

class MobileLoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * Optimizado para aplicaciones móviles
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new Energy4CeroEmail],
            'password' => 'required|string|min:6',
            'device_name' => 'required|string|max:255',
            'device_type' => 'nullable|in:android,ios,web',
            'app_version' => 'nullable|string|max:20',
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
            'device_name.required' => 'El nombre del dispositivo es obligatorio.',
            'device_name.max' => 'El nombre del dispositivo no debe superar los 255 caracteres.',
            'device_type.in' => 'El tipo de dispositivo debe ser: android, ios o web.',
            'app_version.max' => 'La versión de la app no debe superar los 20 caracteres.',
        ]);
    }

    /**
     * Prepare the data for validation
     * Agregar información adicional del dispositivo
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'device_type' => $this->detectDeviceType(),
            'app_version' => $this->header('X-App-Version', '1.0.0'),
        ]);
    }

    /**
     * Detectar tipo de dispositivo basado en User-Agent
     */
    private function detectDeviceType(): string
    {
        $userAgent = $this->header('User-Agent', '');
        
        if (str_contains($userAgent, 'Android')) {
            return 'android';
        }
        
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'ios';
        }
        
        return 'web';
    }
}

