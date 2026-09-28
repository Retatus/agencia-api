<?php

namespace Database\Seeders\Pricing;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = DB::table('currencies')
            ->whereIn('code', ['USD', 'PEN', 'EUR'])
            ->pluck('id', 'code');

        $rates = [
            ['from' => 'USD', 'to' => 'PEN', 'rate' => 3.75000000],
            ['from' => 'USD', 'to' => 'EUR', 'rate' => 0.92000000],
        ];

        foreach ($rates as $rate) {
            DB::table('exchange_rates')->updateOrInsert(
                [
                    'from_currency_id' => $currencies[$rate['from']],
                    'to_currency_id' => $currencies[$rate['to']],
                    'effective_date' => '2026-01-01',
                ],
                [
                    'rate' => $rate['rate'],
                    'source' => 'SEED',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
