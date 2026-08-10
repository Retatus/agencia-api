<?php

namespace App\Pricing\PriceType\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StorePriceTypeRequest extends FormRequest
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
            'code' => 'required|string|max:10|unique:price_types',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es requerido.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no debe tener más de 10 caracteres.',
            'code.unique' => 'Ya existe un tipo de precio con este código.',
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',            
            'name.max' => 'El nombre no debe tener más de 50 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe tener más de 255 caracteres.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
