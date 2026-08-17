<?php

namespace App\Pricing\BasePrice\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBasePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $basePrice =
            $this->route(
                'base_price'
            )
            ?? $this->route(
                'basePrice'
            );

        $basePriceId =
            is_object($basePrice)
                ? $basePrice->id
                : $basePrice;

        return [
            'service_variant_id' => [
                'required',
                'integer',
                'exists:service_variants,id',

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
                    )
                    ->ignore(
                        $basePriceId
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
}