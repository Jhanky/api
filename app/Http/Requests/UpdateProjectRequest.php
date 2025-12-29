<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
        $projectId = $this->route('project') ?? $this->route('id');

        return [
            'code' => 'sometimes|string|max:20|unique:projects,code,' . $projectId,
            'name' => 'sometimes|string|max:100',
            'quotation_id' => 'sometimes|exists:quotations,quotation_id|unique:projects,quotation_id,' . $projectId,
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
            'code.unique' => 'Este código de proyecto ya existe',
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
