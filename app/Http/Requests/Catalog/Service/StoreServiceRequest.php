<?php

namespace App\Http\Requests\Catalog\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'code' => 'required|string|max:10|unique:services',
            'provider_id' => 'required|exists:providers,id',
            'service_categories_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no debe exceder los 10 caracteres.',
            'code.unique' => 'Ya existe un servicio con este código.',
            'provider_id.required' => 'El proveedor es obligatorio.',
            'provider_id.exists' => 'El proveedor seleccionado no existe.',
            'service_categories_id.required' => 'La categoría de servicio es obligatoria.',
            'service_categories_id.exists' => 'La categoría de servicio seleccionada no existe.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 100 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe exceder los 255 caracteres.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
