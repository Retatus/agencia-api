<?php

namespace Database\Seeders\Quotation;

//use App\Models\Service\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;

class QuotationItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('quotation_items')->truncate();

        $itineraries = DB::table('quotation_itineraries')->get();

        foreach ($itineraries as $itinerary) {

            $hotel = Service::where('code', 'SER0001')->first();
            $tour = Service::where('code', 'SER0002')->first();
            $transport = Service::where('code', 'SER0003')->first();

            if ($hotel) {

                DB::table('quotation_items')->insert([
                    'quotation_itinerary_id' => $itinerary->id,
                    'service_id' => $hotel->id,
                    'service_variant_id' => null,
                    'item_type' => 'CATALOG',
                    'name' => $hotel->name,
                    'variant_name' => null,
                    'description' => $hotel->description,
                    'quantity' => 1,
                    'unit_cost' => 80,
                    'unit_price' => 120,
                    'subtotal' => 120,
                    'sort_order' => 1,
                    'active' => true,
                    'calculated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($tour) {

                DB::table('quotation_items')->insert([
                    'quotation_itinerary_id' => $itinerary->id,
                    'service_id' => $tour->id,
                    'service_variant_id' => null,
                    'item_type' => 'CATALOG',
                    'name' => $tour->name,
                    'variant_name' => null,
                    'description' => $tour->description,
                    'quantity' => 2,
                    'price_id' => null,
                    'unit_cost' => 25,
                    'unit_price' => 40,
                    'subtotal' => 80,
                    'sort_order' => 2,
                    'active' => true,
                    'calculated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($transport) {

                DB::table('quotation_items')->insert([
                    'quotation_itinerary_id' => $itinerary->id,
                    'service_id' => $transport->id,
                    'service_variant_id' => null,
                    'item_type' => 'CATALOG',
                    'name' => $transport->name,
                    'variant_name' => null,
                    'description' => $transport->description,
                    'quantity' => 1,
                    'price_id' => null,
                    'unit_cost' => 15,
                    'unit_price' => 30,
                    'subtotal' => 30,
                    'sort_order' => 3,
                    'active' => true,
                    'calculated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Item libre
            DB::table('quotation_items')->insert([
                'quotation_itinerary_id' => $itinerary->id,
                'service_id' => null,
                'service_variant_id' => null,
                'item_type' => 'CUSTOM',
                'name' => 'Botella de Agua',
                'variant_name' => null,
                'description' => 'Cortesía',
                'quantity' => 2,
                'price_id' => null,
                'unit_cost' => 0,
                'unit_price' => 0,
                'subtotal' => 0,
                'sort_order' => 99,
                'active' => true,
                'calculated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}