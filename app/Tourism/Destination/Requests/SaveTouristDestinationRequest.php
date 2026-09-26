<?php

namespace App\Tourism\Destination\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTouristDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $destination = $this->route('touristDestination');

        return [
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('tourist_destinations', 'code')->ignore($destination?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:60'],
            'active' => ['required', 'boolean'],

            'days' => ['required', 'array', 'min:1'],
            'days.*.day_number' => ['required', 'integer', 'min:1'],
            'days.*.title' => ['required', 'string', 'max:150'],
            'days.*.description' => ['nullable', 'string', 'max:255'],
            'days.*.sort_order' => ['required', 'integer', 'min:1'],
            'days.*.items' => ['required', 'array', 'min:1'],

            'days.*.items.*.name' => ['required', 'string', 'max:200'],
            'days.*.items.*.description' => ['nullable', 'string', 'max:255'],
            'days.*.items.*.duration' => ['required', 'integer', 'min:1'],
            'days.*.items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'days.*.items.*.estimated_cost' => ['required', 'numeric', 'min:0'],
            'days.*.items.*.estimated_price' => ['required', 'numeric', 'min:0'],
            'days.*.items.*.sort_order' => ['required', 'integer', 'min:1'],
            'days.*.items.*.active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.max' => 'El código del destino no puede superar 10 caracteres.',
            'code.unique' => 'El código del destino ya está registrado.',
            'days.min' => 'El destino debe tener al menos un día.',
            'days.*.items.min' => 'Cada día debe contener al menos un servicio aproximado.',
        ];
    }
}
