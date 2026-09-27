<?php

namespace App\Pricing\ExchangeRate\Requests;

use App\Pricing\ExchangeRate\Models\ExchangeRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ExchangeRate $exchangeRate */
        $exchangeRate = $this->route('exchange_rate');
        $fromCurrencyId = $this->integer('from_currency_id') ?: $exchangeRate->from_currency_id;
        $toCurrencyId = $this->integer('to_currency_id') ?: $exchangeRate->to_currency_id;

        return [
            'from_currency_id' => ['sometimes', 'integer', 'exists:currencies,id', 'different:to_currency_id'],
            'to_currency_id' => ['sometimes', 'integer', 'exists:currencies,id'],
            'rate' => ['sometimes', 'numeric', 'gt:0'],
            'effective_date' => [
                'sometimes',
                'date',
                Rule::unique('exchange_rates')
                    ->ignore($exchangeRate->getKey())
                    ->where(fn ($query) => $query
                        ->where('from_currency_id', $fromCurrencyId)
                        ->where('to_currency_id', $toCurrencyId)),
            ],
            'source' => ['sometimes', 'string', 'max:30'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
