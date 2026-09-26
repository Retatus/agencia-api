<?php

namespace App\Pricing\PriceListItem\Requests;

use App\Pricing\PriceList\Enums\AdjustmentType;
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
            'price_list_id' => ['sometimes', 'integer', 'exists:price_lists,id'],
            'price_id' => ['sometimes', 'integer', 'exists:prices,id'],
            'adjustment_type' => ['sometimes', Rule::enum(AdjustmentType::class)],
            'cost_adjustment' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2'],
            'sale_adjustment' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
