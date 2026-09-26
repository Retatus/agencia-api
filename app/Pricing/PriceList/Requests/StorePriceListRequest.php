<?php

namespace App\Pricing\PriceList\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceListRequest extends FormRequest
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
            'code' => 'required|unique:price_lists,code',
            'name' => 'required',
            'description' => 'required',
            'currency_id' => 'required|exists:currencies,id',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'priority' => 'sometimes|integer|min:1',
            'is_default' => 'sometimes|boolean',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código de la lista de precios es obligatorio.',
            'code.unique' => 'El código de la lista de precios ya existe.',
            'name.required' => 'El nombre de la lista de precios es obligatorio.',
            'description.required' => 'La descripción de la lista de precios es obligatoria.',
            'currency_id.required' => 'El ID de la moneda es obligatorio.',
            'currency_id.exists' => 'La moneda seleccionada no existe.',
            'valid_from.required' => 'La fecha de inicio de la lista de precios es obligatoria.',
            'valid_from.date' => 'La fecha de inicio de la lista de precios debe ser una fecha.',
            'valid_to.required' => 'La fecha de fin de la lista de precios es obligatoria.',
            'valid_to.date' => 'La fecha de fin de la lista de precios debe ser una fecha.',
            'valid_to.after' => 'La fecha de fin de la lista de precios debe ser posterior a la fecha de inicio.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
