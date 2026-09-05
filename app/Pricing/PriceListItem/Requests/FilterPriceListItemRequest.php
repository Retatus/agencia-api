<?php

namespace App\Pricing\PriceListItem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterPriceListItemRequest extends FormRequest
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
            'active' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
