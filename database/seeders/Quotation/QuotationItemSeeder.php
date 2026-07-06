<?php

namespace Database\Seeders\Quotation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('quotation_items')->insert([

            [
                'quotation_id' => 1,

                'service_variant_id' => 1,

                'price_id' => 1,

                'provider_name' => 'Peru Travel SAC',

                'service_name' => 'City Tour Cusco',

                'variant_name' => 'Servicio Compartido',

                'service_date' => now()->addDays(20),

                'quantity' => 2,

                'unit_cost' => 80,

                'unit_price' => 120,

                'total_cost' => 160,

                'total_price' => 240,

                'remarks' => 'Recojo desde hotel.',

                'sort_order' => 1,

                'calculated_at' => now(),

                'created_at' => now(),

                'updated_at' => now(),
            ],

            [
                'quotation_id' => 1,

                'service_variant_id' => 2,

                'price_id' => 2,

                'provider_name' => 'Hotel Cusco Plaza',

                'service_name' => 'Hospedaje',

                'variant_name' => 'Habitación Doble',

                'service_date' => now()->addDays(20),

                'quantity' => 2,

                'unit_cost' => 90,

                'unit_price' => 150,

                'total_cost' => 180,

                'total_price' => 300,

                'remarks' => '2 noches.',

                'sort_order' => 2,

                'calculated_at' => now(),

                'created_at' => now(),

                'updated_at' => now(),
            ],

            [
                'quotation_id' => 2,

                'service_variant_id' => 3,

                'price_id' => 3,

                'provider_name' => 'Luxury Transport',

                'service_name' => 'Traslado Aeropuerto',

                'variant_name' => 'Privado',

                'service_date' => now()->addDays(35),

                'quantity' => 1,

                'unit_cost' => 50,

                'unit_price' => 80,

                'total_cost' => 50,

                'total_price' => 80,

                'remarks' => null,

                'sort_order' => 1,

                'calculated_at' => now(),

                'created_at' => now(),

                'updated_at' => now(),
            ],

        ]);
    }
}