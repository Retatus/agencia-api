<?php

namespace App\Pricing\BasePrice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBasePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_id' => [
                'sometimes',
                'required',
                'exists:currencies,id',
            ],

            'cost' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'sometimes',
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
}