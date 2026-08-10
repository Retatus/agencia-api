<?php

namespace App\Pricing\Price\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdatePricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prices' => [
                'required',
                'array',
                'min:1',
            ],

            'prices.*.id' => [
                'required',
                'integer',
                'exists:prices,id',
            ],

            'prices.*.cost' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'prices.*.sale_price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'prices.*.active' => [
                'sometimes',
                'boolean',
            ],

            'prices.*.min_quantity' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],

            'prices.*.max_quantity' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'prices.required' =>
                'Debe enviar al menos un precio para actualizar.',

            'prices.array' =>
                'La lista de precios debe ser un arreglo.',

            'prices.min' =>
                'Debe enviar al menos un precio para actualizar.',


            'prices.*.id.required' =>
                'El identificador del precio es obligatorio.',

            'prices.*.id.integer' =>
                'El identificador del precio debe ser válido.',

            'prices.*.id.exists' =>
                'Uno de los precios enviados no existe.',


            'prices.*.cost.numeric' =>
                'El costo debe ser un valor numérico.',

            'prices.*.cost.min' =>
                'El costo no puede ser negativo.',


            'prices.*.sale_price.numeric' =>
                'El precio de venta debe ser un valor numérico.',

            'prices.*.sale_price.min' =>
                'El precio de venta no puede ser negativo.',


            'prices.*.active.boolean' =>
                'El estado del precio debe ser verdadero o falso.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AFTER VALIDATION
    |--------------------------------------------------------------------------
    */

    public function after(): array
    {
        return [
            function ($validator) {

                foreach (
                    $this->input('prices', [])
                    as $index => $price
                ) {
                    if (
                        isset(
                            $price['min_quantity'],
                            $price['max_quantity']
                        )
                        &&
                        $price['min_quantity'] >
                        $price['max_quantity']
                    ) {
                        $validator->errors()->add(
                            "prices.$index.max_quantity",
                            'La cantidad máxima debe ser mayor o igual a la mínima.'
                        );
                    }
                }
            },
        ];
    }
}