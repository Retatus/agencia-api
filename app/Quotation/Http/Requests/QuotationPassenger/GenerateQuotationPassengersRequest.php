<?php

namespace App\Quotation\Http\Requests\QuotationPassenger;

use Illuminate\Foundation\Http\FormRequest;

class GenerateQuotationPassengersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'groups' => [
                'required',
                'array',
                'min:1',
            ],

            'groups.*.passenger_type_id' => [
                'required',
                'integer',
                'exists:passenger_types,id',
            ],

            'groups.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'nationality' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}