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
                'exists:service_variants,id',
            ],

            'adjustment_type' => [
                'required',
                Rule::in([
                    'OVERRIDE',
                    'FIXED',
                    'PERCENTAGE',
                ]),
            ],

            'adjustment_value' => [
                'nullable',
                'numeric',
            ],

            'override_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'override_sale_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $type = $this->input('adjustment_type');

                if ($type === 'OVERRIDE') {
                    if ($this->input('override_sale_price') === null) {
                        $validator->errors()->add(
                            'override_sale_price',
                            'El precio de venta override es obligatorio.'
                        );
                    }
                }

                if (
                    in_array(
                        $type,
                        ['FIXED', 'PERCENTAGE'],
                        true
                    ) &&
                    $this->input('adjustment_value') === null
                ) {
                    $validator->errors()->add(
                        'adjustment_value',
                        'El valor del ajuste es obligatorio.'
                    );
                }
            },
        ];
    }
}