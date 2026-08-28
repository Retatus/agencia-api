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
        [
            'code' => 'PASSENGER',
            'name' => 'Por Pasajero',
            'quantity_basis' => 'PASSENGERS',
        ],
        [
            'code' => 'GROUP',
            'name' => 'Por Grupo',
            'quantity_basis' => 'PASSENGERS',
        ],
        [
            'code' => 'ROOM',
            'name' => 'Por Habitación',
            'quantity_basis' => 'UNITS',
        ],
        [
            'code' => 'FIXED',
            'name' => 'Precio Fijo',
            'quantity_basis' => 'UNITS',
        ],
    ];

    foreach ($types as $type) {
        DB::table('price_types')->updateOrInsert(
            ['code' => $type['code']],
            [
                'name' => $type['name'],
                'quantity_basis' => $type['quantity_basis'],
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
}
