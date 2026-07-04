<?php

namespace App\Http\Requests\Catalog\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('providers')->ignore($this->route('provider')),
            ],
            'business_name' => 'required|string|max:100',
            'commercial_name' => 'nullable|string|max:100',
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('providers')->ignore($this->route('provider')),
            ],
            'tax_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:100',
            'notes' => 'nullable|string|max:255',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.max' => 'El código no debe exceder los 10 caracteres.',
            'code.unique' => 'Ya existe un proveedor con este código.',
            'business_name.required' => 'La razón social es obligatoria.',
            'business_name.string' => 'La razón social debe ser una cadena de texto.',
            'business_name.max' => 'La razón social no debe exceder los 100 caracteres.',
            'commercial_name.string' => 'El nombre comercial debe ser una cadena de texto.',
            'commercial_name.max' => 'El nombre comercial no debe exceder los 100 caracteres.',
            'document_type_id.required' => 'El tipo de documento es obligatorio.',
            'document_type_id.exists' => 'El tipo de documento seleccionado no es válido.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.string' => 'El número de documento debe ser una cadena de texto.',
            'document_number.max' => 'El número de documento no debe exceder los 20 caracteres.',
            'document_number.unique' => 'Ya existe un proveedor con este número de documento.',
            'tax_name.string' => 'El nombre fiscal debe ser una cadena de texto.',
            'tax_name.max' => 'El nombre fiscal no debe exceder los 100 caracteres.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.max' => 'El correo electrónico no debe exceder los 100 caracteres.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.max' => 'El teléfono no debe exceder los 20 caracteres.',
            'website.url' => 'La página web debe ser una URL válida.',
            'website.max' => 'La página web no debe exceder los 100 caracteres.',
            'notes.string' => 'Las notas deben ser una cadena de texto.',
            'notes.max' => 'Las notas no deben exceder los 255 caracteres.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
