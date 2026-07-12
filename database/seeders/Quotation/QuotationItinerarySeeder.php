<?php

namespace Database\Seeders\Quotation;

//use App\Models\App\Quotation\Models\Quotation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Quotation;

class QuotationItinerarySeeder extends Seeder
{
    public function run(): void
    {
        //DB::table('quotation_itineraries')->truncate();

        DB::table('quotation_itineraries')->insert([
          [
              'uuid' => Str::uuid(),
              'quotation_id' => 1,
              'day_number' => 1,
              'travel_date' => now()->addDays(1),
              'title' => 'Llegada',
              'description' => 'Recepción y traslado al hotel.',
              'sort_order' => 1,
              'created_at' => now(),
              'updated_at' => now(),
          ],
          [
              'uuid' => Str::uuid(),
              'quotation_id' => 1,
              'day_number' => 2,
              'travel_date' => now()->addDays(2),
              'title' => 'City Tour',
              'description' => 'Recorrido por la ciudad.',
              'sort_order' => 2,
              'created_at' => now(),
              'updated_at' => now(),
          ],
          [
              'uuid' => Str::uuid(),
              'quotation_id' => 1,
              'day_number' => 3,
              'travel_date' => now()->addDays(3),
              'title' => 'Valle Sagrado',
              'description' => 'Excursión de día completo.',
              'sort_order' => 3,
              'created_at' => now(),
              'updated_at' => now(),
          ],
      ]);
    }
}