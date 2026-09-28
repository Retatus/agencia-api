<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CasonaDelSolHotelSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $documentTypeId = $this->requiredId('document_types', 'RUC');
            $categoryId = $this->requiredId('service_categories', 'HOTEL');
            $currencyId = $this->requiredId('currencies', 'USD');
            $priceTypeId = $this->requiredId('price_types', 'ROOM');

            $providerId = $this->provider($documentTypeId);

            $cuscoId = $this->service(
                providerId: $providerId,
                categoryId: $categoryId,
                code: 'CDS-CUSCO',
                name: 'Casona del Sol Cusco',
                description: 'Hotel boutique de categoría superior ubicado cerca del centro histórico de Cusco. Tarifas por habitación y noche, con desayuno buffet e impuestos incluidos.'
            );

            $valleId = $this->service(
                providerId: $providerId,
                categoryId: $categoryId,
                code: 'CDS-VALLE',
                name: 'Casona del Sol Valle Sagrado',
                description: 'Hotel campestre de categoría superior en el Valle Sagrado. Tarifas por habitación y noche, con desayuno buffet e impuestos incluidos.'
            );

            $rooms = [
                // service, code, name, min, max, optimal, cost, sale
                [$cuscoId, 'CDS-CSGL', 'Habitación Simple Superior', 1, 1, 1, 62, 85],
                [$cuscoId, 'CDS-CDBL', 'Habitación Matrimonial Superior', 1, 2, 2, 78, 108],
                [$cuscoId, 'CDS-CTWN', 'Habitación Twin Superior', 1, 2, 2, 82, 112],
                [$cuscoId, 'CDS-CTPL', 'Habitación Triple Superior', 2, 3, 3, 108, 148],
                [$cuscoId, 'CDS-CFAM', 'Habitación Familiar', 3, 4, 4, 138, 188],
                [$cuscoId, 'CDS-CSUI', 'Suite Junior', 1, 2, 2, 125, 175],

                [$valleId, 'CDS-VSGL', 'Habitación Simple Jardín', 1, 1, 1, 68, 92],
                [$valleId, 'CDS-VDBL', 'Habitación Matrimonial Jardín', 1, 2, 2, 88, 120],
                [$valleId, 'CDS-VTWN', 'Habitación Twin Jardín', 1, 2, 2, 92, 125],
                [$valleId, 'CDS-VTPL', 'Habitación Triple Jardín', 2, 3, 3, 118, 160],
                [$valleId, 'CDS-VFAM', 'Bungalow Familiar', 3, 5, 4, 165, 225],
                [$valleId, 'CDS-VSUI', 'Suite con Terraza', 1, 2, 2, 145, 198],
            ];

            foreach ($rooms as [$serviceId, $code, $name, $min, $max, $optimal, $cost, $sale]) {
                $variantId = $this->variant(
                    serviceId: $serviceId,
                    code: $code,
                    name: $name,
                    min: $min,
                    max: $max,
                    optimal: $optimal
                );

                $this->basePrice(
                    variantId: $variantId,
                    priceTypeId: $priceTypeId,
                    currencyId: $currencyId,
                    cost: $cost,
                    sale: $sale
                );
            }
        });
    }

    private function provider(int $documentTypeId): int
    {
        $current = DB::table('providers')
            ->where('code', 'CASONSOL')
            ->first();

        DB::table('providers')->updateOrInsert(
            ['code' => 'CASONSOL'],
            [
                'uuid' => $current?->uuid ?? (string) Str::uuid(),
                'business_name' => 'Inversiones Hoteleras Casona del Sol S.A.C.',
                'commercial_name' => 'Casona del Sol Hotels',
                'document_type_id' => $documentTypeId,
                'document_number' => '20608876541',
                'tax_name' => 'Inversiones Hoteleras Casona del Sol S.A.C.',
                'email' => 'reservas@casonadelsol.test',
                'phone' => '+51 84 640 280',
                'website' => 'https://www.casonadelsol.test',
                'notes' => 'Proveedor hotelero de demostración para pruebas funcionales de alojamiento, tarifas base, temporadas y promociones.',
                'active' => true,
                'deleted_at' => null,
                'created_at' => $current?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('providers')
            ->where('code', 'CASONSOL')
            ->value('id');
    }

    private function service(
        int $providerId,
        int $categoryId,
        string $code,
        string $name,
        string $description
    ): int {
        $current = DB::table('services')
            ->where('code', $code)
            ->first();

        DB::table('services')->updateOrInsert(
            ['code' => $code],
            [
                'uuid' => $current?->uuid ?? (string) Str::uuid(),
                'provider_id' => $providerId,
                'service_category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'active' => true,
                'created_at' => $current?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('services')
            ->where('code', $code)
            ->value('id');
    }

    private function variant(
        int $serviceId,
        string $code,
        string $name,
        int $min,
        int $max,
        int $optimal
    ): int {
        $current = DB::table('service_variants')
            ->where('code', $code)
            ->first();

        DB::table('service_variants')->updateOrInsert(
            ['code' => $code],
            [
                'service_id' => $serviceId,
                'name' => $name,
                'min_capacity' => $min,
                'max_capacity' => $max,
                'optimal_capacity' => $optimal,
                'unit_type' => 'ROOM',
                'duration' => 1,
                'active' => true,
                'created_at' => $current?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('service_variants')
            ->where('code', $code)
            ->value('id');
    }

    private function basePrice(
        int $variantId,
        int $priceTypeId,
        int $currencyId,
        float $cost,
        float $sale
    ): void {
        $identity = [
            'service_variant_id' => $variantId,
            'price_type_id' => $priceTypeId,
            'passenger_type_id' => null,
            'currency_id' => $currencyId,
            'min_quantity' => null,
            'max_quantity' => null,
        ];

        $current = DB::table('prices')
            ->where($identity)
            ->first();

        DB::table('prices')->updateOrInsert(
            $identity,
            [
                'valid_from' => null,
                'valid_to' => null,
                'cost' => $cost,
                'sale_price' => $sale,
                'priority' => 1,
                'active' => true,
                'created_at' => $current?->created_at ?? now(),
                'updated_at' => now(),
            ]
        );
    }

    private function requiredId(string $table, string $code): int
    {
        $id = (int) DB::table($table)
            ->where('code', $code)
            ->value('id');

        if ($id === 0) {
            throw new RuntimeException(
                "No existe {$table}.code={$code}. Ejecuta primero los seeders base."
            );
        }

        return $id;
    }
}
