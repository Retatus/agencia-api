<?php

namespace App\Pricing\BasePrice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBasePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_id' => [
                'required',
                'exists:currencies,id',
            ],

            'cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'currency_id.required' => 'La moneda es obligatoria.',
            'currency_id.exists' => 'La moneda seleccionada no es válida.',

            'cost.required' => 'El costo es obligatorio.',
            'cost.numeric' => 'El costo debe ser numérico.',
            'cost.min' => 'El costo no puede ser negativo.',

            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.numeric' => 'El precio de venta debe ser numérico.',
            'sale_price.min' => 'El precio de venta no puede ser negativo.',

            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}