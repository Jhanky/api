<?php

namespace App\Http\Requests\Apk\Project;

use App\Http\Requests\Shared\BaseRequest;

class MobileProjectRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', \App\Models\Project::class);
    }

    /**
     * Get the validation rules that apply to the request.
     * Optimizado para consultas móviles
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'client_id' => 'nullable|integer|exists:clients,client_id',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,created_at,updated_at,start_date',
            'sort_order' => 'nullable|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'search.max' => 'La búsqueda no debe superar los 100 caracteres.',
            'status.max' => 'El estado no debe superar los 50 caracteres.',
            'client_id.integer' => 'El ID del cliente debe ser un número entero.',
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'per_page.integer' => 'Los elementos por página debe ser un número entero.',
            'per_page.min' => 'Los elementos por página debe ser al menos 1.',
            'per_page.max' => 'Los elementos por página no debe superar 50.',
            'page.integer' => 'La página debe ser un número entero.',
            'page.min' => 'La página debe ser al menos 1.',
            'sort_by.in' => 'El campo de ordenamiento debe ser: name, created_at, updated_at o start_date.',
            'sort_order.in' => 'El orden debe ser: asc o desc.',
        ]);
    }

    /**
     * Prepare the data for validation
     * Establecer valores por defecto para móvil
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => $this->get('per_page', 15),
            'page' => $this->get('page', 1),
            'sort_by' => $this->get('sort_by', 'updated_at'),
            'sort_order' => $this->get('sort_order', 'desc'),
        ]);
    }
}

