<?php

namespace Database\Seeders\Pricing;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usd = DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        $priceLists = [

            [
                'code'        => 'GENERAL2026',
                'name'        => 'Tarifa General 2026',
                'description' => 'Lista principal para clientes nacionales y extranjeros.',
                'currency_id' => $usd,
                'valid_from'  => '2026-01-01',
                'valid_to'    => '2026-12-31',
                'priority'    => 1,
                'is_default'  => true,
            ],

            [
                'code'        => 'CORP2026',
                'name'        => 'Tarifa Corporativa 2026',
                'description' => 'Precios especiales para empresas.',
                'currency_id' => $usd,
                'valid_from'  => '2026-01-01',
                'valid_to'    => '2026-12-31',
                'priority'    => 2,
                'is_default'  => false,
            ],

            [
                'code'        => 'WHOLESALE2026',
                'name'        => 'Tarifa Mayorista 2026',
                'description' => 'Lista para agencias asociadas y operadores.',
                'currency_id' => $usd,
                'valid_from'  => '2026-01-01',
                'valid_to'    => '2026-12-31',
                'priority'    => 3,
                'is_default'  => false,
            ],

            [
                'code'        => 'PROMOJUL2026',
                'name'        => 'Promoción Julio 2026',
                'description' => 'Campaña promocional de julio.',
                'currency_id' => $usd,
                'valid_from'  => '2026-07-01',
                'valid_to'    => '2026-07-31',
                'priority'    => 10,
                'is_default'  => false,
            ],

        ];

        foreach ($priceLists as $row) {

            DB::table('price_lists')->updateOrInsert(

                [
                    'code' => $row['code']
                ],

                [
                    'name'         => $row['name'],
                    'description'  => $row['description'],
                    'currency_id'  => $row['currency_id'],
                    'valid_from'   => $row['valid_from'],
                    'valid_to'     => $row['valid_to'],
                    'priority'     => $row['priority'],
                    'is_default'   => $row['is_default'],
                    'active'       => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]

            );

        }
    }
}
