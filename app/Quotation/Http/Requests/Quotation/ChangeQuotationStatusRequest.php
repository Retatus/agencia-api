<?php

namespace App\Quotation\Http\Requests\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class ChangeQuotationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_code' => [
                'required',
                'string',
                'in:DRAFT,READY,SENT,CONFIRMED,REJECTED,EXPIRED,CANCELLED',
            ],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
