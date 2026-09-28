<?php

namespace App\Pricing\ExchangeRate\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_currency_id' => ['required', 'integer', 'exists:currencies,id', 'different:to_currency_id'],
            'to_currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'rate' => ['required', 'numeric', 'gt:0'],
            'effective_date' => [
                'required',
                'date',
                Rule::unique('exchange_rates')->where(fn ($query) => $query
                    ->where('from_currency_id', $this->integer('from_currency_id'))
                    ->where('to_currency_id', $this->integer('to_currency_id'))),
            ],
            'source' => ['sometimes', 'string', 'max:30'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
