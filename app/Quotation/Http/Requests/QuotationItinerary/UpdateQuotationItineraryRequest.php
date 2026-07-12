<?php

namespace App\Quotation\Http\Requests\QuotationItinerary;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateQuotationItineraryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quotation_id' => 'integer|exists:quotations,id',
            'day_number' => 'integer|min:1',
            'travel_date' => 'date',
            'title' => 'string|max:255',
            'description' => 'string',
            'sort_order' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
           'quotation_id.required' => 'El campo Quotation es requerido.',
           'day_number.required' => 'El campo Day Number es requerido.',
           'travel_date.required' => 'El campo Travel Date es requerido.',
           'title.required' => 'El campo Title es requerido.',
           'sort_order.required' => 'El campo Sort Order es requerido.',
        ];
    }
}
