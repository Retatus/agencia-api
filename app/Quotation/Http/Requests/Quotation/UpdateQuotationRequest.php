<?php

namespace App\Quotation\Http\Requests\Quotation;

class UpdateQuotationRequest extends StoreQuotationRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    |
    | La cotización se actualiza como agregado completo:
    |
    | Header
    | Passengers
    | Itineraries
    | Items
    |
    | Por eso reutilizamos exactamente las mismas reglas del Store.
    |
    */

    public function rules(): array
    {
        return parent::rules();
    }

    public function after(): array
    {
        return parent::after();
    }

    public function messages(): array
    {
        return parent::messages();
    }
}