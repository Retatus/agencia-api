<?php

namespace App\Pricing\PriceListItem\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePriceListItemRequest extends FormRequest
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
}