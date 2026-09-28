<?php

namespace App\Tourism\Destination\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertTouristDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'exchange_rate_date' => ['nullable', 'date'],
        ];
    }
}
