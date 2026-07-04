<?php

namespace App\Http\Requests\Shared\Currency;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
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
                'max:3',
                'unique:currencies,code'
            ],

            'symbol' => [
                'required',
                'string',
                'max:5'
            ],

            'name' => [
                'required',
                'string',
                'max:50'
            ],

            'decimals' => [
                'required',
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
            'code.required' => 'El código de la moneda es obligatorio.',
            'code.max' => 'El código no puede tener más de 3 caracteres.',
            'code.unique' => 'Ya existe una moneda con este código.',

            'symbol.required' => 'El símbolo es obligatorio.',
            'symbol.max' => 'El símbolo no puede tener más de 5 caracteres.',

            'name.required' => 'El nombre de la moneda es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 50 caracteres.',

            'decimals.required' => 'Debes indicar la cantidad de decimales.',
            'decimals.integer' => 'Los decimales deben ser un número entero.',
            'decimals.between' => 'Los decimales deben estar entre 0 y 6.',

            'is_base.boolean' => 'El valor de is_base debe ser booleano.',
            'active.boolean' => 'El valor de active debe ser booleano.',
        ];
    }
}
