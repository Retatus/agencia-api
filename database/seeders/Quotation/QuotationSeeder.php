<?php

namespace Database\Seeders\Quotation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('quotations')->insert([

            [
                'uuid' => Str::uuid(),
                'code' => 'COT-000001',

                'customer_id' => 1,
                'price_list_id' => 1,
                'currency_id' => 1,
                'quotation_status_id' => 1,

                'exchange_rate' => 3.75,

                'travel_date' => now()->addDays(20),

                'valid_until' => now()->addDays(10),

                'notes' => 'Cotización familiar a Cusco.',

                'subtotal' => 420.00,
                'discount' => 20.00,
                'tax' => 72.00,
                'total' => 472.00,

                'active' => true,

                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'uuid' => Str::uuid(),
                'code' => 'COT-000002',

                'customer_id' => 2,
                'price_list_id' => 1,
                'currency_id' => 1,
                'quotation_status_id' => 3,

                'exchange_rate' => 3.75,

                'travel_date' => now()->addDays(35),

                'valid_until' => now()->addDays(15),

                'notes' => 'Tour privado.',

                'subtotal' => 980.00,
                'discount' => 0,
                'tax' => 176.40,
                'total' => 1156.40,

                'active' => true,

                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}