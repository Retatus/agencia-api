<?php

namespace Database\Seeders\Pricing;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'PASSENGER', 'name' => 'Por Pasajero'],
            ['code' => 'GROUP',     'name' => 'Por Grupo'],
            ['code' => 'ROOM',      'name' => 'Por Habitación'],
            ['code' => 'FIXED',     'name' => 'Precio Fijo'],
        ];

        foreach ($types as $type) {
            DB::table('price_types')->updateOrInsert(
                ['code' => $type['code']],
                [
                    'name'       => $type['name'],
                    'active'     => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
