<?php

namespace App\Pricing\Price\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $search = trim(
            (string) $this->input(
                'search',
                ''
            )
        );

        $this->merge([
            'search' => $search ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'service_variant_id' => [
                'nullable',
                'integer',
                'exists:service_variants,id',
            ],

            'service_uuid' => [
                'nullable',
                'uuid',
                'exists:services,uuid',
            ],

            'provider_id' => [
                'nullable',
                'integer',
                'exists:providers,id',
            ],

            'service_category_id' => [
                'nullable',
                'integer',
                'exists:service_categories,id',
            ],

            'price_type_id' => [
                'nullable',
                'integer',
                'exists:price_types,id',
            ],

            'passenger_type_id' => [
                'nullable',
                'integer',
                'exists:passenger_types,id',
            ],

            'currency_id' => [
                'nullable',
                'integer',
                'exists:currencies,id',
            ],

            /*
             * Se valida porque PriceList existe,
             * aunque su filtro todavía no se aplica
             * hasta implementar PriceAdjustment.
             */
            'price_list_id' => [
                'nullable',
                'integer',
                'exists:price_lists,id',
            ],

            'active' => [
                'nullable',
                'boolean',
            ],

            'date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'cost_from' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cost_to' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:cost_from',
            ],

            'sale_from' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'sale_to' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:sale_from',
            ],

            'min_quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'max_quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'sort' => [
                'nullable',
                'string',
                'in:cost,sale_price,min_quantity,max_quantity,created_at',
            ],

            'direction' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'search.max' =>
                'La búsqueda no puede superar los 100 caracteres.',

            'service_variant_id.exists' =>
                'La variante seleccionada no existe.',

            'service_uuid.exists' =>
                'El servicio seleccionado no existe.',

            'provider_id.exists' =>
                'El proveedor seleccionado no existe.',

            'service_category_id.exists' =>
                'La categoría seleccionada no existe.',

            'price_type_id.exists' =>
                'El tipo de precio seleccionado no existe.',

            'passenger_type_id.exists' =>
                'El tipo de pasajero seleccionado no existe.',

            'currency_id.exists' =>
                'La moneda seleccionada no existe.',

            'price_list_id.exists' =>
                'La lista de precios seleccionada no existe.',

            'active.boolean' =>
                'El estado activo debe ser verdadero o falso.',

            'date.date_format' =>
                'La fecha debe usar el formato YYYY-MM-DD.',

            'cost_to.gte' =>
                'El costo final debe ser mayor o igual al costo inicial.',

            'sale_to.gte' =>
                'El precio final debe ser mayor o igual al precio inicial.',

            'sort.in' =>
                'El campo de ordenamiento no es válido.',

            'direction.in' =>
                'La dirección debe ser asc o desc.',

            'per_page.max' =>
                'No se pueden solicitar más de 100 registros por página.',
        ];
    }
}