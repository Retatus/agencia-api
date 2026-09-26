<?php

namespace App\Pricing\PriceListItem\Requests;

use App\Pricing\PriceList\Enums\AdjustmentType;
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
            'price_list_id' => ['required', 'integer', 'exists:price_lists,id'],
            'price_id' => ['required', 'integer', 'exists:prices,id'],
            'adjustment_type' => ['required', Rule::enum(AdjustmentType::class)],
            'cost_adjustment' => [
                'nullable', 'numeric', 'decimal:0,2',
                'required_without:sale_adjustment',
            ],
            'sale_adjustment' => [
                'nullable', 'numeric', 'decimal:0,2',
                'required_without:cost_adjustment',
            ],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
