<?php

namespace App\Quotation\Http\Requests\QuotationPassenger;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateQuotationPassengersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passengers' => [
                'required',
                'array',
                'min:1',
            ],

            'passengers.*.uuid' => [
                'required',
                'uuid',
            ],

            'passengers.*.passenger_type_id' => [
                'sometimes',
                'integer',
                'exists:passenger_types,id',
            ],

            'passengers.*.nationality' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'passengers.*.active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}