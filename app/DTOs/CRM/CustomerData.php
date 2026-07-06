<?php

namespace App\DTOs\CRM;

use Illuminate\Http\Request;

class CustomerData
{
    public function __construct(
        public readonly ?string $uuid,
        public readonly int $document_type_id,
        public readonly string $document_number,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly ?string $birth_date,
        public readonly ?string $gender,
        public readonly ?string $nationality,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $notes,
        public readonly bool $active,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            uuid: $request->input('uuid'),
            document_type_id: $request->integer('document_type_id'),
            document_number: $request->input('document_number'),
            first_name: $request->input('first_name'),
            last_name: $request->input('last_name'),
            birth_date: $request->input('birth_date'),
            gender: $request->input('gender'),
            nationality: $request->input('nationality'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            address: $request->input('address'),
            city: $request->input('city'),
            country: $request->input('country'),
            notes: $request->input('notes'),
            active: $request->boolean('active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid'              => $this->uuid,
            'document_type_id'  => $this->document_type_id,
            'document_number'   => $this->document_number,
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'birth_date'        => $this->birth_date,
            'gender'            => $this->gender,
            'nationality'       => $this->nationality,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'address'           => $this->address,
            'city'              => $this->city,
            'country'           => $this->country,
            'notes'             => $this->notes,
            'active'            => $this->active,
        ];
    }
}