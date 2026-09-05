<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommercialPricingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $currencyId = DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        $variantId = DB::table('service_variants')
            ->where('code', 'AT-GUIAPR')
            ->value('id');

        if (!$currencyId || !$variantId) {
            return;
        }

        $current = DB::table('price_lists')
            ->where('code', 'PROMO-GUIA')
            ->first();

        DB::table('price_lists')->updateOrInsert(
            ['code' => 'PROMO-GUIA'],
            [
                'uuid' => $current?->uuid ?? (string) Str::uuid(),
                'name' => 'Promoción Guías -10%',
                'description' => 'Promoción funcional para probar ajustes sobre tarifas grupales.',
                'currency_id' => $currencyId,
                'valid_from' => '2026-01-01',
                'valid_to' => '2026-12-31',
                'priority' => 10,
                'is_default' => false,
                'active' => true,
                'created_at' => $current?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );

        $priceListId = DB::table('price_lists')
            ->where('code', 'PROMO-GUIA')
            ->value('id');

        $priceIds = DB::table('prices')
            ->where('service_variant_id', $variantId)
            ->pluck('id');

        foreach ($priceIds as $priceId) {
            DB::table('price_list_items')->updateOrInsert(
                [
                    'price_list_id' => $priceListId,
                    'price_id' => $priceId,
                ],
                [
                    'adjustment_type' => 'PERCENTAGE',
                    'cost_adjustment' => null,
                    'sale_adjustment' => -10,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
