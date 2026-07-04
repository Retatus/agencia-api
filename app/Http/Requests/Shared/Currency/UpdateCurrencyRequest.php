<?php

namespace App\Http\Requests\Shared\Currency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
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
                'unique:currencies,code,' . $this->route('currency')->id
            ],

            'symbol' => [
                'sometimes',
                'string',
                'max:5'
            ],

            'name' => [
                'sometimes',
                'string',
                'max:50'
            ],

            'decimals' => [
                'sometimes',
                'integer',
                'between:0,6'
            ],

            'is_base' => [
                'boolean'
            ],

            'active' => [
                'boolean'
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Ya existe una moneda con este código.',
            'decimals.between' => 'Los decimales deben estar entre 0 y 6.',
            'boolean' => 'Este campo debe ser booleano.',
        ];
    }
}
