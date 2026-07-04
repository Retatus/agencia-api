<?php

namespace App\Http\Requests\DocumentType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentTypeRequest extends FormRequest
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
            'code' => 'sometimes|string|size:10|unique:document_types,code,' . $this->route('document_type')->id,
            'name' => 'sometimes|string|max:50',
            'active' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'code.size' => 'El código debe tener exactamente 10 caracteres.',
            'code.unique' => 'Ya existe un tipo de documento con este código.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 50 caracteres.',
            'active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
