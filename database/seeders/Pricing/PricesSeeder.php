<?php

namespace Database\Seeders\Pricing;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricesSeeder extends Seeder
{
    public function run(): void
    {
        $currencyId = DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        if (!$currencyId) {
            throw new \Exception('Currency USD no existe.');
        }

        // Variantes de servicio (DBL, TPL, VAN, ADULT, CHILD, STUDENT)
        $variants = DB::table('service_variants')
            ->pluck('id', 'code');

        // tipos de precio (PASSENGER, GROUP, ROOM, FIXED)
        $priceTypes = DB::table('price_types')
            ->pluck('id', 'code');

        // Tipos de pasajero (ADT, CHD, INF, STD)
        $passengerTypes = DB::table('passenger_types')
            ->pluck('id', 'code');

        // Lista de precios
        $prices = [

            /*
            |--------------------------------------------------------------------------
            | HOTEL (ROOM)
            |--------------------------------------------------------------------------
            */

            [
                'variant' => 'DBL',
                'price' => 'ROOM',
                'cost' => 95,
                'sale' => 120,
            ],

            [
                'variant' => 'TPL',
                'price' => 'ROOM',
                'cost' => 135,
                'sale' => 170,
            ],

            /*
            |--------------------------------------------------------------------------
            | VAN (GROUP)
            |--------------------------------------------------------------------------
            */

            [
                'variant' => 'VAN',
                'price' => 'GROUP',
                'min' => 1,
                'max' => 4,
                'cost' => 90,
                'sale' => 120,
            ],

            [
                'variant' => 'VAN',
                'price' => 'GROUP',
                'min' => 5,
                'max' => 7,
                'cost' => 120,
                'sale' => 150,
            ],

            [
                'variant' => 'VAN',
                'price' => 'GROUP',
                'min' => 8,
                'max' => 10,
                'cost' => 145,
                'sale' => 180,
            ],

            /*
            |--------------------------------------------------------------------------
            | MACHUPICCHU (PASSENGER)
            |--------------------------------------------------------------------------
            */

            [
                'variant' => 'ADULT',
                'price' => 'PASSENGER',
                'passenger' => 'ADT',
                'cost' => 120,
                'sale' => 150,
            ],

            [
                'variant' => 'CHILD',
                'price' => 'PASSENGER',
                'passenger' => 'CHD',
                'cost' => 70,
                'sale' => 90,
            ],

            [
                'variant' => 'STUDENT',
                'price' => 'PASSENGER',
                'passenger' => 'STD',
                'cost' => 95,
                'sale' => 120,
            ],

        ];

        foreach ($prices as $row) {

            // Validación de variante
            $variantId = $variants[$row['variant']] ?? null;

            if (!$variantId) {
                throw new \Exception("Variant code '{$row['variant']}' no existe en service_variants.");
            }

            // Validación de passenger_type (solo si aplica)
            $passengerId = null;

            if ($row['price'] === 'PASSENGER') {

                if (!isset($row['passenger'])) {
                    throw new \Exception("PASSENGER price requiere 'passenger' en el arreglo.");
                }

                $passengerId = $passengerTypes[$row['passenger']] ?? null;

                if (!$passengerId) {
                    throw new \Exception("Passenger type '{$row['passenger']}' no existe en passenger_types.");
                }
            }

            // Insertar o actualizar
            DB::table('prices')->updateOrInsert(
                [
                    'service_variant_id' => $variantId,
                    'price_type_id' => $priceTypes[$row['price']] ?? null,
                    'passenger_type_id' => $passengerId ?? null,
                    'currency_id' => $currencyId,
                    'min_quantity' => $row['min'] ?? null,
                    'max_quantity' => $row['max'] ?? null,
                ],
                [
                    'valid_from' => null,
                    'valid_to' => null,
                    'cost' => $row['cost'],
                    'sale_price' => $row['sale'],
                    'priority' => 1,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
