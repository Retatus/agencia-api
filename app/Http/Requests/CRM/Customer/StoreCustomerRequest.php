<?php

namespace App\Http\Requests\CRM\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'uuid' => 'required|uuid',
            'document_type_id' => 'required|exists:document_types,id',
            'document_number' => 'required|unique:customers,document_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'uuid.required' => 'El campo uuid es obligatorio.',
            'uuid.uuid' => 'El campo uuid debe ser un UUID válido.',
            'document_type_id.required' => 'El campo document_type_id es obligatorio.',
            'document_type_id.exists' => 'El document_type_id proporcionado no existe.',
            'document_number.required' => 'El campo document_number es obligatorio.',
            'document_number.unique' => 'El document_number proporcionado ya está en uso.',
            'first_name.required' => 'El campo first_name es obligatorio.',
            'last_name.required' => 'El campo last_name es obligatorio.',
            'email.email' => 'El campo email debe ser una dirección de correo electrónico válida.',
            'email.unique' => 'El email proporcionado ya está en uso.',
        ];
    }
}
