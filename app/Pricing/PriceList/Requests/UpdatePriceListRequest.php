<?php

namespace App\Pricing\PriceList\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdatePriceListRequest extends FormRequest
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
            'name' => 'sometimes',
            'description' => 'sometimes',
            'currency_id' => 'sometimes|exists:currencies,id',
            'valid_from' => 'sometimes|date',
            'valid_to' => 'sometimes|date|after:valid_from',
            'priority' => 'sometimes|integer|min:1',
            'is_default' => 'sometimes|boolean',
            'active' => 'boolean',
        ];
    }

   
    public function messages(): array
    {
        return [
            'name.sometimes' => 'El nombre de la lista de precios es obligatorio.',
            'description.sometimes' => 'La descripción de la lista de precios es obligatoria.',
            'currency_id.sometimes' => 'El ID de la moneda es obligatorio.',
            'currency_id.exists' => 'La moneda seleccionada no existe.',
            'valid_from.sometimes' => 'La fecha de inicio de la lista de precios es obligatoria.',
            'valid_from.date' => 'La fecha de inicio de la lista de precios debe ser una fecha.',
            'valid_to.sometimes' => 'La fecha de fin de la lista de precios es obligatoria.',
            'valid_to.date' => 'La fecha de fin de la lista de precios debe ser una fecha.',
            'valid_to.after' => 'La fecha de fin de la lista de precios debe ser posterior a la fecha de inicio.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
