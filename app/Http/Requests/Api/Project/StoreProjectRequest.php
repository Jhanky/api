<?php

namespace App\Http\Requests\Api\Project;

use App\Http\Requests\Shared\BaseRequest;

class StoreProjectRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Project::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'quotation_id' => 'required|integer|exists:quotations,quotation_id',
            'client_id' => 'required|integer|exists:clients,client_id',
            'location_id' => 'required|integer|exists:locations,location_id',
            'status_id' => 'required|integer|exists:project_statuses,status_id',
            'project_name' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'estimated_end_date' => 'required|date|after:start_date',
            'project_manager_id' => 'nullable|integer|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'installation_address' => 'nullable|string|max:500',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'cover_image_alt' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'quotation_id.required' => 'La cotización es obligatoria.',
            'quotation_id.exists' => 'La cotización seleccionada no existe.',
            'client_id.required' => 'El cliente es obligatorio.',
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'location_id.required' => 'La ubicación es obligatoria.',
            'location_id.exists' => 'La ubicación seleccionada no existe.',
            'status_id.required' => 'El estado del proyecto es obligatorio.',
            'status_id.exists' => 'El estado seleccionado no existe.',
            'project_name.required' => 'El nombre del proyecto es obligatorio.',
            'project_name.max' => 'El nombre del proyecto no debe superar los 255 caracteres.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'estimated_end_date.required' => 'La fecha estimada de finalización es obligatoria.',
            'estimated_end_date.after' => 'La fecha estimada de finalización debe ser posterior a la fecha de inicio.',
            'project_manager_id.exists' => 'El gerente de proyecto seleccionado no existe.',
            'budget.numeric' => 'El presupuesto debe ser un número.',
            'budget.min' => 'El presupuesto debe ser mayor o igual a 0.',
            'notes.max' => 'Las notas no deben superar los 1000 caracteres.',
            'latitude.numeric' => 'La latitud debe ser un número.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90 grados.',
            'longitude.numeric' => 'La longitud debe ser un número.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180 grados.',
            'installation_address.max' => 'La dirección de instalación no debe superar los 500 caracteres.',
            'cover_image.image' => 'El archivo debe ser una imagen.',
            'cover_image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif.',
            'cover_image.max' => 'La imagen no debe superar los 5MB.',
            'cover_image_alt.max' => 'El texto alternativo no debe superar los 255 caracteres.',
        ]);
    }
}

