<?php

namespace App\Quotation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Cabecera
            |--------------------------------------------------------------------------
            */

            'customer_id' => ['required','exists:customers,id'],

            'currency_id' => ['required','exists:currencies,id'],

            'price_list_id' => ['required','exists:price_lists,id'],

            'travel_date' => ['required','date'],

            'valid_until' => ['required','date'],

            'notes' => ['nullable','string'],

            /*
            |--------------------------------------------------------------------------
            | Pasajeros
            |--------------------------------------------------------------------------
            */

            'passengers' => ['required','array','min:1'],

            'passengers.*.passenger_type_id'
                => ['required','exists:passenger_types,id'],

            'passengers.*.first_name'
                => ['required','string','max:100'],

            'passengers.*.last_name'
                => ['required','string','max:100'],

            'passengers.*.birth_date'
                => ['nullable','date'],

            'passengers.*.document_number'
                => ['nullable','string','max:30'],

            'passengers.*.nationality'
                => ['nullable','string','max:100'],

            'passengers.*.email'
                => ['nullable','email'],

            'passengers.*.phone'
                => ['nullable','string','max:50'],

            /*
            |--------------------------------------------------------------------------
            | Servicios
            |--------------------------------------------------------------------------
            */

            'items' => ['required','array','min:1'],

            'items.*.service_variant_id'
                => ['required','exists:service_variants,id'],

            'items.*.service_date'
                => ['required','date'],

            'items.*.quantity'
                => ['required','numeric','min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists' => 'El cliente no existe.',

            'currency_id.required' => 'La moneda es obligatoria.',
            'currency_id.exists' => 'La moneda no existe.',

            'price_list_id.required' => 'La lista de precios es obligatoria.',
            'price_list_id.exists' => 'La lista de precios no existe.',

            'travel_date.required' => 'La fecha de viaje es obligatoria.',
            'travel_date.date' => 'La fecha de viaje debe ser una fecha válida.',

            'valid_until.required' => 'La fecha de vigencia es obligatoria.',
            'valid_until.date' => 'La fecha de vigencia debe ser una fecha válida.',

            'passengers.required' => 'Debe agregar al menos un pasajero.',
            'passengers.array' => 'Los pasajeros deben ser un arreglo.',
            'passengers.min' => 'Debe agregar al menos un pasajero.',

            'items.required' => 'Debe agregar al menos un servicio.',
            'items.array' => 'Los servicios deben ser un arreglo.',
            'items.min' => 'Debe agregar al menos un servicio.',
        ];
    }
}