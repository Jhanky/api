<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
        $id = $this->route('invoice'); // Obtener ID de la ruta

        return [
            'invoice_number' => "required|string|max:100|unique:invoices,invoice_number,{$id},invoice_id",
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'amount_before_iva' => 'required|numeric|min:0',
            'retention' => 'nullable|numeric|min:0',
            'has_retention' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:pendiente,pagada,cotizacion',
            'sale_type' => 'required|in:CONTADO,CREDITO',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'provider_id' => 'required|exists:suppliers,supplier_id',
            'cost_center_id' => 'required|exists:cost_centers,cost_center_id',
            'payment_support' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'invoice_number.unique' => 'La factura ya fue registrada en la base de datos',
            'status.in' => 'El estado seleccionado no es válido (pendiente, pagada, cotizacion)',
        ];
    }
}
