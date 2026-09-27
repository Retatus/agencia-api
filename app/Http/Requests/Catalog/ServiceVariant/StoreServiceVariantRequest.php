<?php

namespace App\Http\Requests\Catalog\ServiceVariant;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceVariantRequest extends FormRequest
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
            'service_id' => 'required|exists:services,id',
            'code' => 'required|string|max:10|unique:service_variants,code',
            'name' => 'required|string|max:150',
            'min_capacity' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|gte:min_capacity',
            'optimal_capacity' => 'required|integer|gte:min_capacity|lte:max_capacity',
            'unit_type' => 'required|in:PERSON,ROOM,VEHICLE,GROUP,UNIT',
            'duration' => 'nullable|integer|min:1',
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
            'unit_type.required' => 'El tipo de unidad es obligatorio.',
            'unit_type.string' => 'El tipo de unidad debe ser una cadena de texto.',
            'unit_type.max' => 'El tipo de unidad no debe exceder los 255 caracteres.',
            'duration.required' => 'La duración es obligatoria.',
            'duration.numeric' => 'La duración debe ser un número.',
            'duration.min' => 'La duración no puede ser negativa.',
            'active.required' => 'El estado activo es obligatorio.',
            'active.boolean' => 'El estado activo debe ser verdadero o falso.',
        ];
    }
}
