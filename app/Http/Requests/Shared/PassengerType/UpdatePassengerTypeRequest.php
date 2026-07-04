<?php

namespace App\Http\Requests\Shared\PassengerType;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePassengerTypeRequest extends FormRequest
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
                'sometimes',
                'string',
                'max:3',
                'unique:passenger_types,code,' . $this->route('passenger_type')->id
            ],

            'name' => [
                'sometimes',
                'string',
                'max:50'
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],

            'active' => [
                'boolean'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Ya existe un tipo de pasajero con este código.',
            'code.max' => 'El código no debe exceder los 3 caracteres.',
            'name.max' => 'El nombre no debe exceder los 50 caracteres.',
            'description.max' => 'La descripción no debe exceder los 255 caracteres.',
            'boolean' => 'Este campo debe ser booleano.',
        ];
    }
}
