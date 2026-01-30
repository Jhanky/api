<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
        return [
            'nic' => 'required|string|max:50|unique:clients,nic',
            'client_type_id' => 'required|exists:client_types,id',
            'name' => 'required|string|max:100',
            'document_type' => 'required|string|max:20',
            'document_number' => 'required|string|max:50|unique:clients,document_number',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string',
            'monthly_consumption_kwh' => 'required|numeric|min:0',
            'tariff_cop_kwh' => 'required|numeric|min:0',
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
