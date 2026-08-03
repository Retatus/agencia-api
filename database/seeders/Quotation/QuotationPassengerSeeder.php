<?php

namespace Database\Seeders\Quotation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationPassengerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('quotation_passengers')->insert([

            [
                'uuid' => Str::uuid(),

                'quotation_id' => 1,

                'passenger_type_id' => 1,

                'first_name' => 'Juan',

                'last_name' => 'Pérez',

                'birth_date' => '1990-05-10',

                'document_number' => '45879632',

                'nationality' => 'Peruana',

                'email' => 'juan@test.com',

                'phone' => '999111222',

                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'uuid' => Str::uuid(),

                'quotation_id' => 1,

                'passenger_type_id' => 2,

                'first_name' => 'María',

                'last_name' => 'Pérez',

                'birth_date' => '2016-02-18',

                'document_number' => '74125896',

                'nationality' => 'Peruana',

                'email' => null,

                'phone' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'uuid' => Str::uuid(),

                'quotation_id' => 2,

                'passenger_type_id' => 1,

                'first_name' => 'John',

                'last_name' => 'Smith',

                'birth_date' => '1984-09-08',

                'document_number' => 'PA458796',

                'nationality' => 'USA',

                'email' => 'john@test.com',

                'phone' => '+120255501',

                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}