<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'symbol' => '$',
                'name' => 'Dólar Americano',
                'decimals' => 2,
                'is_base' => true,
                'active' => true
            ],
            [
                'code' => 'PEN',
                'symbol' => 'S/',
                'name' => 'Sol Peruano',
                'decimals' => 2,
                'is_base' => false,
                'active' => true
            ],
            [
                'code' => 'EUR',
                'symbol' => '€',
                'name' => 'Euro',
                'decimals' => 2,
                'is_base' => false,
                'active' => true
            ]
        ];

        foreach ($currencies as $currency) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $currency['code']],
                [
                    'symbol'     => $currency['symbol'],
                    'name'       => $currency['name'],
                    'decimals'   => $currency['decimals'],
                    'is_base'    => $currency['is_base'],
                    'active'     => $currency['active'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
