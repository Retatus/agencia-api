<?php

namespace App\Pricing\PriceListItem\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceListItemRequest extends FormRequest
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
            ],

            'adjustment_type' => [
                'required',
                'string',

                Rule::in([
                    'PERCENTAGE',
                    'FIXED',
                    'OVERRIDE',
                ]),
            ],

            'adjustment_value' => [
                'required',
                'numeric',
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
            'service_variant_id.required' =>
                'La variante es obligatoria.',

            'service_variant_id.exists' =>
                'La variante seleccionada no existe.',

            'adjustment_type.required' =>
                'El tipo de ajuste es obligatorio.',

            'adjustment_type.in' =>
                'El tipo de ajuste seleccionado no es válido.',

            'adjustment_value.required' =>
                'El valor del ajuste es obligatorio.',

            'adjustment_value.numeric' =>
                'El valor del ajuste debe ser numérico.',

            'active.boolean' =>
                'El estado debe ser verdadero o falso.',
        ];
    }
}