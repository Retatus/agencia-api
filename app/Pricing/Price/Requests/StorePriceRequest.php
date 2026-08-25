<?php

namespace App\Pricing\Price\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceRequest extends FormRequest
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
            'service_variant_id' => 'required|exists:service_variants,id',
            'price_type_id' => 'required|exists:price_types,id',
            'passenger_type_id' => 'nullable|exists:passenger_types,id',
            'currency_id' => 'required|exists:currencies,id',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date_format:Y-m-d',
            'valid_to' => 'nullable|date_format:Y-m-d',
            'cost' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'priority' => 'sometimes|integer|min:1',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'service_variant_id.required' => 'El ID de la variante de servicio es obligatorio.',
            'service_variant_id.exists' => 'La variante de servicio seleccionada no existe.',
            'price_type_id.required' => 'El ID del tipo de precio es obligatorio.',
            'price_type_id.exists' => 'El tipo de precio seleccionado no existe.',
            'passenger_type_id.exists' => 'El tipo de pasajero seleccionado no existe.',
            'currency_id.required' => 'La moneda es obligatoria.',
            'currency_id.exists' => 'La moneda seleccionada no existe.',
            'min_quantity.integer' => 'La cantidad mínima debe ser un número entero.',
            'min_quantity.min' => 'La cantidad mínima debe ser al menos 1.',
            'max_quantity.integer' => 'La cantidad máxima debe ser un número entero.',
            'max_quantity.min' => 'La cantidad máxima debe ser al menos 1.',
            'valid_from.date_format' => 'La fecha inicial debe usar el formato YYYY-MM-DD.',
            'valid_to.date_format' => 'La fecha final debe usar el formato YYYY-MM-DD.',
            'cost.required' => 'El costo es obligatorio.',
            'cost.numeric' => 'El costo debe ser un número válido.',
            'cost.min' => 'El costo no puede ser negativo.',
            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.numeric' => 'El precio de venta debe ser un número válido.',
            'sale_price.min' => 'El precio de venta no puede ser negativo.',
            'priority.integer' => 'La prioridad debe ser un número entero.',
            'priority.min' => 'La prioridad debe ser al menos 1.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $min = $this->input('min_quantity');
                $max = $this->input('max_quantity');
                $from = $this->input('valid_from');
                $to = $this->input('valid_to');

                if ($min !== null && $max !== null && (int) $min > (int) $max) {
                    $validator->errors()->add(
                        'max_quantity',
                        'La cantidad máxima debe ser mayor o igual a la mínima.'
                    );
                }

                if ($from !== null && $to !== null && $to < $from) {
                    $validator->errors()->add(
                        'valid_to',
                        'La fecha final debe ser posterior o igual a la fecha inicial.'
                    );
                }
            },
        ];
    }
}
