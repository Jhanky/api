<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationRequest extends FormRequest
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
            'client_id' => 'sometimes|exists:clients,id',
            'user_id' => 'sometimes|exists:users,id',
            'project_name' => 'sometimes|string|max:200',
            'system_type_id' => 'sometimes|exists:system_types,id',
            'grid_type_id' => 'sometimes|exists:grid_types,id',
            'power_kwp' => 'sometimes|numeric|min:0.1',
            'panel_count' => 'sometimes|integer|min:1',
            'requires_financing' => 'sometimes|boolean',
            'profit_percentage' => 'sometimes|numeric|min:0|max:1',
            'iva_profit_percentage' => 'sometimes|numeric|min:0|max:1',
            'commercial_management_percentage' => 'sometimes|numeric|min:0|max:1',
            'administration_percentage' => 'sometimes|numeric|min:0|max:1',
            'contingency_percentage' => 'sometimes|numeric|min:0|max:1',
            'withholding_percentage' => 'sometimes|numeric|min:0|max:1',
            'status_id' => 'sometimes|exists:quotation_statuses,id',
            'subtotal' => 'sometimes|numeric|min:0',
            'profit' => 'sometimes|numeric|min:0',
            'profit_iva' => 'sometimes|numeric|min:0',
            'commercial_management' => 'sometimes|numeric|min:0',
            'administration' => 'sometimes|numeric|min:0',
            'contingency' => 'sometimes|numeric|min:0',
            'withholdings' => 'sometimes|numeric|min:0',
            'total_value' => 'sometimes|numeric|min:0',
            'subtotal2' => 'sometimes|numeric|min:0',
            'subtotal3' => 'sometimes|numeric|min:0',
            'products' => 'sometimes|array',
            'products.*.product_type' => 'required_with:products|in:panel,inverter,battery',
            'products.*.product_id' => 'required_with:products|integer',
            'products.*.quantity' => 'required_with:products|integer|min:1',
            'products.*.unit_price_cop' => 'required_with:products|numeric|min:0',
            'products.*.profit_percentage' => 'required_with:products|numeric|min:0|max:1',
            'items' => 'sometimes|array',
            'items.*.description' => 'sometimes|string|max:500',
            'items.*.category' => 'sometimes|string|max:50',
            'items.*.quantity' => 'sometimes|numeric|min:0.01',
            'items.*.unit_measure' => 'sometimes|string|max:20',
            'items.*.unit_price_cop' => 'sometimes|numeric|min:0',
            'items.*.profit_percentage' => 'sometimes|numeric|min:0|max:1',
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
            'client_id.exists' => 'El cliente seleccionado no existe',
            'user_id.exists' => 'El vendedor seleccionado no existe',
            'project_name.string' => 'El nombre del proyecto debe ser texto',
            'project_name.max' => 'El nombre del proyecto no puede exceder los 200 caracteres',
            'system_type_id.exists' => 'El tipo de sistema seleccionado no existe',
            'grid_type_id.exists' => 'El tipo de red seleccionado no existe',
            'power_kwp.numeric' => 'La potencia debe ser un número',
            'power_kwp.min' => 'La potencia debe ser mayor a 0.1 kWp',
            'panel_count.integer' => 'El número de paneles debe ser un entero',
            'panel_count.min' => 'Debe haber al menos 1 panel',
            'profit_percentage.numeric' => 'El porcentaje de utilidad debe ser un número',
            'profit_percentage.min' => 'El porcentaje de utilidad no puede ser negativo',
            'profit_percentage.max' => 'El porcentaje de utilidad no puede exceder el 100%',
            'iva_profit_percentage.numeric' => 'El porcentaje de IVA de utilidad debe ser un número',
            'iva_profit_percentage.min' => 'El porcentaje de IVA de utilidad no puede ser negativo',
            'iva_profit_percentage.max' => 'El porcentaje de IVA de utilidad no puede exceder el 100%',
            'commercial_management_percentage.numeric' => 'El porcentaje de gestión comercial debe ser un número',
            'commercial_management_percentage.min' => 'El porcentaje de gestión comercial no puede ser negativo',
            'commercial_management_percentage.max' => 'El porcentaje de gestión comercial no puede exceder el 100%',
            'administration_percentage.numeric' => 'El porcentaje de administración debe ser un número',
            'administration_percentage.min' => 'El porcentaje de administración no puede ser negativo',
            'administration_percentage.max' => 'El porcentaje de administración no puede exceder el 100%',
            'contingency_percentage.numeric' => 'El porcentaje de imprevistos debe ser un número',
            'contingency_percentage.min' => 'El porcentaje de imprevistos no puede ser negativo',
            'contingency_percentage.max' => 'El porcentaje de imprevistos no puede exceder el 100%',
            'withholding_percentage.numeric' => 'El porcentaje de retención debe ser un número',
            'withholding_percentage.min' => 'El porcentaje de retención no puede ser negativo',
            'withholding_percentage.max' => 'El porcentaje de retención no puede exceder el 100%',
            'status_id.exists' => 'El estado seleccionado no existe',
            'subtotal.numeric' => 'El subtotal debe ser un número',
            'subtotal.min' => 'El subtotal no puede ser negativo',
            'profit.numeric' => 'La utilidad debe ser un número',
            'profit.min' => 'La utilidad no puede ser negativa',
            'profit_iva.numeric' => 'El IVA de utilidad debe ser un número',
            'profit_iva.min' => 'El IVA de utilidad no puede ser negativo',
            'commercial_management.numeric' => 'La gestión comercial debe ser un número',
            'commercial_management.min' => 'La gestión comercial no puede ser negativa',
            'administration.numeric' => 'La administración debe ser un número',
            'administration.min' => 'La administración no puede ser negativa',
            'contingency.numeric' => 'Los imprevistos deben ser un número',
            'contingency.min' => 'Los imprevistos no pueden ser negativos',
            'withholdings.numeric' => 'Las retenciones deben ser un número',
            'withholdings.min' => 'Las retenciones no pueden ser negativas',
            'total_value.numeric' => 'El valor total debe ser un número',
            'total_value.min' => 'El valor total no puede ser negativo',
            'subtotal2.numeric' => 'El subtotal 2 debe ser un número',
            'subtotal2.min' => 'El subtotal 2 no puede ser negativo',
            'subtotal3.numeric' => 'El subtotal 3 debe ser un número',
            'subtotal3.min' => 'El subtotal 3 no puede ser negativo',
            'products.array' => 'Los productos deben ser un arreglo',
            'products.*.product_type.required_with' => 'El tipo de producto es obligatorio',
            'products.*.product_type.in' => 'El tipo de producto debe ser panel, inverter o battery',
            'products.*.product_id.required_with' => 'El ID del producto es obligatorio',
            'products.*.product_id.integer' => 'El ID del producto debe ser un entero',
            'products.*.quantity.required_with' => 'La cantidad es obligatoria',
            'products.*.quantity.integer' => 'La cantidad debe ser un entero',
            'products.*.quantity.min' => 'La cantidad debe ser al menos 1',
            'products.*.unit_price_cop.required_with' => 'El precio unitario es obligatorio',
            'products.*.unit_price_cop.numeric' => 'El precio unitario debe ser un número',
            'products.*.unit_price_cop.min' => 'El precio unitario no puede ser negativo',
            'products.*.profit_percentage.required_with' => 'El porcentaje de utilidad del producto es obligatorio',
            'products.*.profit_percentage.numeric' => 'El porcentaje de utilidad del producto debe ser un número',
            'products.*.profit_percentage.min' => 'El porcentaje de utilidad del producto no puede ser negativo',
            'products.*.profit_percentage.max' => 'El porcentaje de utilidad del producto no puede exceder el 100%',
            'items.array' => 'Los items deben ser un arreglo',
            'items.*.description.string' => 'La descripción del item debe ser texto',
            'items.*.description.max' => 'La descripción del item no puede exceder los 500 caracteres',
            'items.*.category.string' => 'La categoría del item debe ser texto',
            'items.*.category.max' => 'La categoría del item no puede exceder los 50 caracteres',
            'items.*.quantity.numeric' => 'La cantidad del item debe ser un número',
            'items.*.quantity.min' => 'La cantidad del item debe ser mayor a 0.01',
            'items.*.unit_measure.string' => 'La unidad de medida debe ser texto',
            'items.*.unit_measure.max' => 'La unidad de medida no puede exceder los 20 caracteres',
            'items.*.unit_price_cop.numeric' => 'El precio unitario del item debe ser un número',
            'items.*.unit_price_cop.min' => 'El precio unitario del item no puede ser negativo',
            'items.*.profit_percentage.numeric' => 'El porcentaje de utilidad del item debe ser un número',
            'items.*.profit_percentage.min' => 'El porcentaje de utilidad del item no puede ser negativo',
            'items.*.profit_percentage.max' => 'El porcentaje de utilidad del item no puede exceder el 100%',
        ];
    }
}
