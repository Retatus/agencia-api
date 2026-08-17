<?php

namespace App\Pricing\BasePrice\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBasePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_variant_id' => [
                'required',
                'integer',
                'exists:service_variants,id',

                /*
                |--------------------------------------------------------------------------
                | Una variante + moneda debería tener un solo BasePrice activo/base.
                |--------------------------------------------------------------------------
                */

                Rule::unique(
                    'base_prices',
                    'service_variant_id'
                )
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'currency_id',
                                $this->input(
                                    'currency_id'
                                )
                            )
                    ),
            ],

            'currency_id' => [
                'required',
                'integer',
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
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'service_variant_id.required' =>
                'La variante es obligatoria.',

            'service_variant_id.exists' =>
                'La variante seleccionada no es válida.',

            'service_variant_id.unique' =>
                'Ya existe un precio base para esta variante y moneda.',

            'currency_id.required' =>
                'La moneda es obligatoria.',

            'currency_id.exists' =>
                'La moneda seleccionada no es válida.',

            'cost.required' =>
                'El costo es obligatorio.',

            'cost.numeric' =>
                'El costo debe ser numérico.',

            'cost.min' =>
                'El costo no puede ser negativo.',

            'sale_price.required' =>
                'El precio base es obligatorio.',

            'sale_price.numeric' =>
                'El precio base debe ser numérico.',

            'sale_price.min' =>
                'El precio base no puede ser negativo.',

            'active.boolean' =>
                'El campo activo debe ser verdadero o falso.',
        ];
    }
}