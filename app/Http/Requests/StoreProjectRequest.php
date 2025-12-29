<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:projects,code',
            'name' => 'required|string|max:100',
            'quotation_id' => 'required|exists:quotations,quotation_id|unique:projects,quotation_id',
            'status_id' => 'sometimes|exists:project_states,status_id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código del proyecto es obligatorio',
            'code.unique' => 'Este código de proyecto ya existe',
            'name.required' => 'El nombre del proyecto es obligatorio',
            'quotation_id.required' => 'La cotización es obligatoria',
            'quotation_id.exists' => 'La cotización seleccionada no existe',
            'quotation_id.unique' => 'Esta cotización ya está asignada a otro proyecto',
            'status_id.exists' => 'El estado seleccionado no es válido',
            'end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
            'notes.max' => 'Las notas no pueden exceder los 1000 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'código del proyecto',
            'name' => 'nombre del proyecto',
            'quotation_id' => 'cotización',
            'status_id' => 'estado',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'notes' => 'notas',
        ];
    }
}
