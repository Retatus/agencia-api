<?php

namespace App\Pricing\Price\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price_list_id' => 'required|exists:price_lists,id',
            'service_variant_id' => 'required|exists:service_variants,id',
            'price_type_id' => 'required|exists:price_types,id',
            'passenger_type_id' => 'required|exists:passenger_types,id',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'required|integer|min:1|gte:min_quantity',
            'cost' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'price_list_id.required' => 'El ID de la lista de precios es obligatorio.',
            'price_list_id.exists' => 'La lista de precios seleccionada no existe.',
            'service_variant_id.required' => 'El ID de la variante de servicio es obligatorio.',
            'service_variant_id.exists' => 'La variante de servicio seleccionada no existe.',
            'price_type_id.required' => 'El ID del tipo de precio es obligatorio.',
            'price_type_id.exists' => 'El tipo de precio seleccionado no existe.',
            'passenger_type_id.required' => 'El ID del tipo de pasajero es obligatorio.',
            'passenger_type_id.exists' => 'El tipo de pasajero seleccionado no existe.',
            'min_quantity.required' => 'La cantidad mínima es obligatoria.',
            'min_quantity.integer' => 'La cantidad mínima debe ser un número entero.',
            'min_quantity.min' => 'La cantidad mínima debe ser al menos 1.',
            'max_quantity.required' => 'La cantidad máxima es obligatoria.',
            'max_quantity.integer' => 'La cantidad máxima debe ser un número entero.',
            'max_quantity.min' => 'La cantidad máxima debe ser al menos 1.',
            'max_quantity.gte' => 'La cantidad máxima debe ser mayor o igual a la cantidad mínima.',
            'cost.required' => 'El costo es obligatorio.',
            'cost.numeric' => 'El costo debe ser un número válido.',
            'cost.min' => 'El costo no puede ser negativo.',
            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.numeric' => 'El precio de venta debe ser un número válido.',
            'sale_price.min' => 'El precio de venta no puede ser negativo.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
