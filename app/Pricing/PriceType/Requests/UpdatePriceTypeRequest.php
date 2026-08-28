<?php

namespace App\Pricing\PriceType\Requests;

use App\Pricing\PriceType\Enums\QuantityBasis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePriceTypeRequest extends FormRequest
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
            'code' => 'sometimes|string|max:10|unique:price_types',
            'name' => 'sometimes|string|max:50',
            'description' => 'nullable|string|max:255',
            'quantity_basis' => ['sometimes', Rule::enum(QuantityBasis::class)],
            'active' => 'boolean',
        ];
    }
   
    public function messages(): array
    {
        return [
            'code.sometimes' => 'El código es requerido.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no debe tener más de 10 caracteres.',
            'code.unique' => 'Ya existe un tipo de precio con este código.',
            'name.sometimes' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',            
            'name.max' => 'El nombre no debe tener más de 50 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe tener más de 255 caracteres.',
            'quantity_basis.enum' => 'La base tarifaria debe ser PASSENGERS o UNITS.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
