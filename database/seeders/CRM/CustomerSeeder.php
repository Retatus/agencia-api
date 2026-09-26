<?php

namespace Database\Seeders\CRM;

use App\Models\CRM\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [

            [
                'document_type_id' => 1,
                'document_number' => '45879632',
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'birth_date' => '1988-03-12',
                'gender' => 'M',
                'nationality' => 'PE',
                'email' => 'juan.perez@test.com',
                'phone' => '999111222',
                'address' => 'Av. Larco 123',
                'city' => 'Lima',
                'country' => 'Perú',
            ],

            [
                'document_type_id' => 1,
                'document_number' => '74125896',
                'first_name' => 'María',
                'last_name' => 'Torres',
                'birth_date' => '1994-09-25',
                'gender' => 'F',
                'nationality' => 'PE',
                'email' => 'maria.torres@test.com',
                'phone' => '988555666',
                'address' => 'Jr. Los Olivos 245',
                'city' => 'Cusco',
                'country' => 'Perú',
            ],

            [
                'document_type_id' => 2,
                'document_number' => 'PA458796',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'birth_date' => '1982-11-20',
                'gender' => 'M',
                'nationality' => 'US',
                'email' => 'john.smith@test.com',
                'phone' => '+12025550111',
                'address' => '742 Evergreen Ave',
                'city' => 'Miami',
                'country' => 'Estados Unidos',
            ],

        ];

        foreach ($customers as $customer) {

            Customer::create([
                'uuid' => Str::uuid(),
                'active' => true,
                ...$customer,
            ]);

        }
    }
}