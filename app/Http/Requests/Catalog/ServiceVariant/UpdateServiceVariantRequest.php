<?php

namespace App\Http\Requests\Catalog\ServiceVariant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceVariantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'code' => 'required|unique:service_variants,code,' . $this->route('service_variant')->id,
            'name' => 'required|string|max:255',
            'min_capacity' => 'required|numeric|min:0',
            'max_capacity' => 'required|numeric|min:0',
            'optimal_capacity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',    
            'unit_type' => 'required|string|max:255',
            'duration' => 'required|numeric|min:0',
            'active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'El ID del servicio es obligatorio.',
            'service_id.exists' => 'El ID del servicio no existe.',
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'El código ya está en uso.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'min_capacity.required' => 'La capacidad mínima es obligatoria.',
            'min_capacity.numeric' => 'La capacidad mínima debe ser un número.',
            'min_capacity.min' => 'La capacidad mínima no puede ser negativa.',
            'max_capacity.required' => 'La capacidad máxima es obligatoria.',
            'max_capacity.numeric' => 'La capacidad máxima debe ser un número.',
            'max_capacity.min' => 'La capacidad máxima no puede ser negativa.',
            'optimal_capacity.required' => 'La capacidad óptima es obligatoria.',
            'optimal_capacity.numeric' => 'La capacidad óptima debe ser un número.',
            'optimal_capacity.min' => 'La capacidad óptima no puede ser negativa.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',    
            'unit_type.required' => 'El tipo de unidad es obligatorio.',

        ];
    }
}
