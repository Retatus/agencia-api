<?php

namespace App\Http\Requests\Catalog\ServiceCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceCategoryRequest extends FormRequest
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
            'code' => 'sometimes|string|max:10|unique:service_categories,code,' . $this->route('service_category'),
            'name' => 'sometimes|string|max:100',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no debe exceder los 10 caracteres.',
            'code.unique' => 'Ya existe una categoría de servicio con este código.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 100 caracteres.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
