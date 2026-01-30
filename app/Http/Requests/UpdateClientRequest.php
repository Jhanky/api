<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $clientId = $this->route('client'); // Assumes route parameter is 'client' or uses ID directly

        return [
            'nic' => 'sometimes|required|string|max:50|unique:clients,nic,' . $clientId,
            'client_type_id' => 'sometimes|required|exists:client_types,id',
            'name' => 'sometimes|required|string|max:100',
            'document_type' => 'sometimes|required|string|max:20',
            'document_number' => 'sometimes|required|string|max:50|unique:clients,document_number,' . $clientId,
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'department_id' => 'sometimes|required|exists:departments,id',
            'city_id' => 'sometimes|required|exists:cities,id',
            'address' => 'sometimes|required|string',
            'monthly_consumption_kwh' => 'sometimes|required|numeric|min:0',
            'tariff_cop_kwh' => 'sometimes|required|numeric|min:0',
            'responsible_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'primary_contact' => 'nullable|array',
            'primary_contact.name' => 'required_with:primary_contact|string|max:100',
            'primary_contact.email' => 'nullable|email|max:100',
            'primary_contact.phone' => 'nullable|string|max:20',
            'primary_contact.position' => 'nullable|string|max:100'
        ];
    }
}
