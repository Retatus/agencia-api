<?php

namespace App\Quotation\Http\Requests\Quotation;

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
            | HEADER
            |--------------------------------------------------------------------------
            */

            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],

            'quotation_status_id' => [
                'required',
                'integer',
                'exists:quotation_statuses,id',
            ],

            'exchange_rate' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'travel_date' => [
                'required',
                'date',
            ],

            'valid_until' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | PASSENGERS
            |--------------------------------------------------------------------------
            */

            'passengers' => [
                'nullable',
                'array',
            ],

            'passengers.*.uuid' => [
                'nullable',
                'string',
            ],

            'passengers.*.passenger_type_id' => [
                'required',
                'integer',
                'exists:passenger_types,id',
            ],

            'passengers.*.first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'passengers.*.last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'passengers.*.birth_date' => [
                'nullable',
                'date',
            ],

            'passengers.*.nationality' => [
                'nullable',
                'string',
                'max:100',
            ],

            'passengers.*.document_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'passengers.*.email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'passengers.*.phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'passengers.*.sort_order' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'passengers.*.active' => [
                'sometimes',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | ITINERARIES
            |--------------------------------------------------------------------------
            */

            'itineraries' => [
                'nullable',
                'array',
            ],

            'itineraries.*.uuid' => [
                'nullable',
                'string',
            ],

            'itineraries.*.day_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'itineraries.*.travel_date' => [
                'nullable',
                'date',
            ],

            'itineraries.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'itineraries.*.description' => [
                'nullable',
                'string',
            ],

            'itineraries.*.sort_order' => [
                'nullable',
                'integer',
                'min:1',
            ],

            /*
            |--------------------------------------------------------------------------
            | ITEMS
            |--------------------------------------------------------------------------
            */

            'itineraries.*.items' => [
                'nullable',
                'array',
            ],

            'itineraries.*.items.*.uuid' => [
                'nullable',
                'string',
            ],

            'itineraries.*.items.*.item_type' => [
                'required',
                'string',
                'in:CATALOG,CUSTOM',
            ],

            'itineraries.*.items.*.service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],

            'itineraries.*.items.*.service_variant_id' => [
                'nullable',
                'integer',
                'exists:service_variants,id',
            ],

            'itineraries.*.items.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'itineraries.*.items.*.variant_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'itineraries.*.items.*.description' => [
                'nullable',
                'string',
            ],

            'itineraries.*.items.*.duration' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'itineraries.*.items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | SOLO RELEVANTES PARA CUSTOM
            |--------------------------------------------------------------------------
            */

            'itineraries.*.items.*.unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'itineraries.*.items.*.unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'itineraries.*.items.*.sort_order' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'itineraries.*.items.*.notes' => [
                'nullable',
                'string',
            ],

            'itineraries.*.items.*.active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {

                foreach (
                    $this->input('itineraries', [])
                    as $itineraryIndex => $itinerary
                ) {

                    foreach (
                        $itinerary['items'] ?? []
                        as $itemIndex => $item
                    ) {

                        $path =
                            "itineraries.$itineraryIndex.items.$itemIndex";

                        /*
                        |--------------------------------------------------------------------------
                        | CATALOG
                        |--------------------------------------------------------------------------
                        */

                        if (
                            ($item['item_type'] ?? null)
                            === 'CATALOG'
                        ) {
                            if (
                                empty(
                                    $item['service_variant_id']
                                )
                            ) {
                                $validator
                                    ->errors()
                                    ->add(
                                        "$path.service_variant_id",
                                        'La variante es obligatoria para un servicio de catálogo.'
                                    );
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | CUSTOM
                        |--------------------------------------------------------------------------
                        */

                        if (
                            ($item['item_type'] ?? null)
                            === 'CUSTOM'
                        ) {
                            if (
                                ! array_key_exists(
                                    'unit_price',
                                    $item
                                ) ||
                                $item['unit_price'] === null
                            ) {
                                $validator
                                    ->errors()
                                    ->add(
                                        "$path.unit_price",
                                        'El precio de venta es obligatorio para un ítem manual.'
                                    );
                            }
                        }
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' =>
                'El cliente es obligatorio.',

            'currency_id.required' =>
                'La moneda es obligatoria.',

            'quotation_status_id.required' =>
                'El estado de la cotización es obligatorio.',

            'travel_date.required' =>
                'La fecha de viaje es obligatoria.',

            'exchange_rate.gt' =>
                'El tipo de cambio debe ser mayor que cero.',

            'passengers.*.passenger_type_id.required' =>
                'El tipo de pasajero es obligatorio.',

            'itineraries.*.day_number.required' =>
                'El número de día es obligatorio.',

            'itineraries.*.items.*.item_type.required' =>
                'El tipo de ítem es obligatorio.',

            'itineraries.*.items.*.item_type.in' =>
                'El tipo de ítem debe ser CATALOG o CUSTOM.',

            'itineraries.*.items.*.name.required' =>
                'El nombre del ítem es obligatorio.',

            'itineraries.*.items.*.quantity.required' =>
                'La cantidad es obligatoria.',

            'itineraries.*.items.*.quantity.gt' =>
                'La cantidad debe ser mayor que cero.',
        ];
    }
}