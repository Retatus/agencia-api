<?php

namespace App\Quotation\Http\Requests\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationRequest extends FormRequest
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

            'travel_date' => ['required','date'],

            'valid_until' => ['required','date'],

            'notes' => ['nullable','string'],

            /*
            |--------------------------------------------------------------------------
            | Pasajeros
            |--------------------------------------------------------------------------
            */

            'passengers' => ['required','array','min:1'],

            'passengers.*.id' => ['sometimes','exists:quotation_passengers,id'],

            /*
            | UUID generado por frontend.
            |
            | No usamos exists porque un pasajero nuevo
            | puede tener UUID generado por JavaScript.
            */

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

            'passengers.*.notes' 
                => ['nullable','string','max:255'],

            /*
            |--------------------------------------------------------------------------
            | Itinerarios
            |--------------------------------------------------------------------------
            */

            'itineraries' => ['required','array','min:1'],

            'itineraries.*.id' => ['sometimes','nullable','exists:quotation_itineraries,id'],

            /*
            | UUID generado por frontend.
            |
            | Para registros existentes:
            | UUID corresponde a la BD.
            |
            | Para registros nuevos:
            | UUID es generado por crypto.randomUUID().
            */

            'itineraries.*.uuid' 
                => ['required','uuid'],

            'itineraries.*.day_number' 
                => ['required','numeric','min:1'],

            'itineraries.*.travel_date' 
                => ['sometimes','date'],

            'itineraries.*.title' 
                => ['sometimes','string','max:255'],

            'itineraries.*.description' 
                => ['nullable','string','max:255'],

            'itineraries.*.sort_order' 
                => ['required','numeric','min:1'],

            /*
            |--------------------------------------------------------------------------
            | No confiamos en subtotal enviado por frontend.
            |--------------------------------------------------------------------------
            |
            | Aunque permitimos recibirlo para mantener compatibilidad
            | con el payload actual, CalculateQuotationTotalsAction
            | debe recalcularlo.
            |
            */

            'itineraries.*.subtotal' => ['sometimes','numeric','min:0'],

            /*
            |--------------------------------------------------------------------------
            | Servicios / Items
            |--------------------------------------------------------------------------
            */

            'itineraries.*.items' => ['required','array','min:1'],

            'itineraries.*.items.*.id' 
                => ['sometimes', 'nullable', 'exists:quotation_items,id'],

            /*
            | UUID del item.
            |
            | El frontend lo genera tanto para items existentes
            | como para items nuevos.
            */

            'itineraries.*.items.*.uuid' 
                => ['required','uuid'],

            'itineraries.*.items.*.service_id' 
                => ['nullable','exists:services,id'],

            'itineraries.*.items.*.service_variant_id' 
                => ['nullable','exists:service_variants,id'],

            'itineraries.*.items.*.item_type' 
                => ['required','in:CATALOG,CUSTOM'],

            'itineraries.*.items.*.calculation_type' => [
                'nullable',
                'string',
                'in:generic,accommodation,transport',
            ],

            'itineraries.*.items.*.group_uuid' => [
                'nullable',
                'uuid',
            ],

            'itineraries.*.items.*.group_index' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'itineraries.*.items.*.name' 
                => ['required','string','max:255',
            ],

            'itineraries.*.items.*.variant_name' 
                => ['nullable','string','max:255',
            ],

            'itineraries.*.items.*.description' 
                => ['nullable','string','max:255',
            ],

            'itineraries.*.items.*.duration' 
                => ['nullable','numeric','min:1',
            ],

            'itineraries.*.items.*.quantity' 
                => ['required','numeric','min:1',
            ],

            'itineraries.*.items.*.unit_cost' 
                => ['required','numeric','min:0',
            ],

            'itineraries.*.items.*.base_cost'
                => ['sometimes','numeric','min:0'],

            'itineraries.*.items.*.base_price'
                => ['sometimes','numeric','min:0'],

            'itineraries.*.items.*.price_id' 
                => ['nullable','exists:prices,id'],

            'itineraries.*.items.*.unit_price' 
                => ['required','numeric','min:0'],

            /*
            | No deberíamos confiar en subtotal.
            | El backend debe recalcularlo.
            */

            'itineraries.*.items.*.subtotal' 
                => ['sometimes','numeric','min:0'],

            'itineraries.*.items.*.subtotal_cost'
                => ['sometimes','numeric','min:0'],

            'itineraries.*.items.*.subtotal_sale'
                => ['sometimes','numeric','min:0'],

            'itineraries.*.items.*.sort_order' 
                => ['required','numeric','min:1'],

            'itineraries.*.items.*.notes' 
                => ['nullable','string','max:255'],

            'itineraries.*.items.*.active' 
                => ['required','boolean'],
        ];
    }

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Cabecera
            |--------------------------------------------------------------------------
            */

            'customer_id.required'
                => 'El cliente es obligatorio.',

            'customer_id.exists'
                => 'El cliente no existe.',

            'currency_id.required'
                => 'La moneda es obligatoria.',

            'currency_id.exists'
                => 'La moneda no existe.',

            'travel_date.required'
                => 'La fecha de viaje es obligatoria.',

            'travel_date.date'
                => 'La fecha de viaje debe ser una fecha válida.',

            'valid_until.required'
                => 'La fecha de vigencia es obligatoria.',

            'valid_until.date'
                => 'La fecha de vigencia debe ser una fecha válida.',

            /*
            |--------------------------------------------------------------------------
            | Pasajeros
            |--------------------------------------------------------------------------
            */

            'passengers.required'
                => 'Debe agregar al menos un pasajero.',

            'passengers.array'
                => 'Los pasajeros deben ser un arreglo.',

            'passengers.min'
                => 'Debe agregar al menos un pasajero.',

            'passengers.*.passenger_type_id.required'
                => 'El tipo de pasajero es obligatorio.',

            'passengers.*.passenger_type_id.exists'
                => 'El tipo de pasajero no existe.',

            'passengers.*.first_name.required'
                => 'El nombre del pasajero es obligatorio.',

            'passengers.*.first_name.string'
                => 'El nombre del pasajero debe ser una cadena de texto.',

            'passengers.*.first_name.max'
                => 'El nombre del pasajero debe tener menos de 100 caracteres.',

            'passengers.*.last_name.required'
                => 'El apellido del pasajero es obligatorio.',

            'passengers.*.last_name.string'
                => 'El apellido del pasajero debe ser una cadena de texto.',

            'passengers.*.last_name.max'
                => 'El apellido del pasajero debe tener menos de 100 caracteres.',

            'passengers.*.birth_date.date'
                => 'La fecha de nacimiento debe ser una fecha válida.',

            'passengers.*.email.email'
                => 'El correo electrónico no es válido.',

            /*
            |--------------------------------------------------------------------------
            | Itinerarios
            |--------------------------------------------------------------------------
            */

            'itineraries.required'
                => 'Debe agregar al menos un itinerario.',

            'itineraries.array'
                => 'Los itinerarios deben ser un arreglo.',

            'itineraries.min'
                => 'Debe agregar al menos un itinerario.',

            'itineraries.*.uuid.required'
                => 'El UUID del itinerario es obligatorio.',

            'itineraries.*.uuid.uuid'
                => 'El UUID del itinerario no es válido.',

            'itineraries.*.day_number.required'
                => 'El número de día es obligatorio.',

            'itineraries.*.day_number.numeric'
                => 'El número de día debe ser un número.',

            'itineraries.*.day_number.min'
                => 'El número de día debe ser mayor o igual a 1.',

            'itineraries.*.travel_date.date'
                => 'La fecha de viaje debe ser una fecha válida.',

            'itineraries.*.title.string'
                => 'El título debe ser una cadena de texto.',

            'itineraries.*.title.max'
                => 'El título debe tener menos de 255 caracteres.',

            'itineraries.*.description.string'
                => 'La descripción debe ser una cadena de texto.',

            'itineraries.*.description.max'
                => 'La descripción debe tener menos de 255 caracteres.',

            'itineraries.*.sort_order.required'
                => 'El orden es obligatorio.',

            'itineraries.*.sort_order.numeric'
                => 'El orden debe ser un número.',

            'itineraries.*.sort_order.min'
                => 'El orden debe ser mayor o igual a 1.',

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */

            'itineraries.*.items.required'
                => 'Debe agregar al menos un servicio.',

            'itineraries.*.items.array'
                => 'Los servicios deben ser un arreglo.',

            'itineraries.*.items.min'
                => 'Debe agregar al menos un servicio.',

            'itineraries.*.items.*.uuid.required'
                => 'El UUID del servicio es obligatorio.',

            'itineraries.*.items.*.uuid.uuid'
                => 'El UUID del servicio no es válido.',

            'itineraries.*.items.*.name.required'
                => 'El nombre es obligatorio.',

            'itineraries.*.items.*.name.string'
                => 'El nombre debe ser una cadena de texto.',

            'itineraries.*.items.*.name.max'
                => 'El nombre debe tener menos de 255 caracteres.',

            'itineraries.*.items.*.description.string'
                => 'La descripción debe ser una cadena de texto.',

            'itineraries.*.items.*.description.max'
                => 'La descripción debe tener menos de 255 caracteres.',

            'itineraries.*.items.*.quantity.required'
                => 'La cantidad es obligatoria.',

            'itineraries.*.items.*.quantity.numeric'
                => 'La cantidad debe ser un número.',

            'itineraries.*.items.*.quantity.min'
                => 'La cantidad debe ser mayor o igual a 1.',

            'itineraries.*.items.*.unit_cost.required'
                => 'El costo unitario es obligatorio.',

            'itineraries.*.items.*.unit_cost.numeric'
                => 'El costo unitario debe ser un número.',

            'itineraries.*.items.*.unit_cost.min'
                => 'El costo unitario debe ser mayor o igual a 0.',

            'itineraries.*.items.*.unit_price.required'
                => 'El precio unitario es obligatorio.',

            'itineraries.*.items.*.unit_price.numeric'
                => 'El precio unitario debe ser un número.',

            'itineraries.*.items.*.unit_price.min'
                => 'El precio unitario debe ser mayor o igual a 0.',

            'itineraries.*.items.*.sort_order.required'
                => 'El orden es obligatorio.',

            'itineraries.*.items.*.sort_order.numeric'
                => 'El orden debe ser un número.',

            'itineraries.*.items.*.sort_order.min'
                => 'El orden debe ser mayor o igual a 1.',

            'itineraries.*.items.*.active.boolean'
                => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
