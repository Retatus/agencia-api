<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class FrontendPricingScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $documentTypeId = (int) DB::table('document_types')->value('id');
            $currencyId = (int) DB::table('currencies')->where('code', 'USD')->value('id');

            if ($documentTypeId === 0 || $currencyId === 0) {
                throw new RuntimeException(
                    'Ejecuta primero los seeders de document_types y currencies.'
                );
            }

            $categoryIds = $this->categoryIds();
            $priceTypeIds = $this->priceTypeIds();
            $passengerTypeIds = $this->passengerTypeIds();
            $providerId = $this->provider($documentTypeId);

            $hotelId = $this->service(
                code: 'DEM-HOTEL',
                providerId: $providerId,
                categoryId: $categoryIds['HOTEL'],
                name: 'Hotel Demo Pricing Cusco',
                description: 'Hotel de pruebas para recomendaciones de habitaciones.',
            );

            $transportId = $this->service(
                code: 'DEM-TRANS',
                providerId: $providerId,
                categoryId: $categoryIds['TRANSPORT'],
                name: 'Transporte Demo Pricing Cusco',
                description: 'Transporte de pruebas para recomendaciones por capacidad.',
            );

            $ticketId = $this->service(
                code: 'DEM-TICK',
                providerId: $providerId,
                categoryId: $categoryIds['TICKET'],
                name: 'Entrada Demo Centro Arqueológico',
                description: 'Entrada de pruebas con tarifas por tipo de pasajero.',
            );

            $variants = [
                'D-SGL' => $this->variant($hotelId, 'D-SGL', 'Demo Habitación Simple', 1, 1, 1, 'ROOM'),
                'D-DBL' => $this->variant($hotelId, 'D-DBL', 'Demo Habitación Doble', 2, 2, 2, 'ROOM'),
                'D-TPL' => $this->variant($hotelId, 'D-TPL', 'Demo Habitación Triple', 3, 3, 3, 'ROOM'),

                'D-AUTO' => $this->variant($transportId, 'D-AUTO', 'Demo Auto', 1, 3, 3, 'VEHICLE'),
                'D-VAN' => $this->variant($transportId, 'D-VAN', 'Demo Van', 4, 10, 10, 'VEHICLE'),
                'D-BUS30' => $this->variant($transportId, 'D-BUS30', 'Demo Bus 30', 11, 30, 30, 'VEHICLE'),

                'D-ADT' => $this->variant($ticketId, 'D-ADT', 'Demo Adulto', 1, 1, 1, 'PERSON'),
                'D-CHD' => $this->variant($ticketId, 'D-CHD', 'Demo Niño', 1, 1, 1, 'PERSON'),
            ];

            DB::table('prices')
                ->whereIn('service_variant_id', array_values($variants))
                ->delete();

            $this->insertPrices(
                variants: $variants,
                priceTypes: $priceTypeIds,
                passengerTypes: $passengerTypeIds,
                currencyId: $currencyId,
            );
        });
    }

    /** @return array<string, int> */
    private function categoryIds(): array
    {
        $categories = [
            'HOTEL' => 'Hotel',
            'TRANSPORT' => 'Transporte',
            'TICKET' => 'Ticket',
        ];

        foreach ($categories as $code => $name) {
            DB::table('service_categories')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        return DB::table('service_categories')
            ->whereIn('code', array_keys($categories))
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /** @return array<string, int> */
    private function priceTypeIds(): array
    {
        $required = [
            'ROOM' => 'UNITS',
            'GROUP' => 'PASSENGERS',
            'PASSENGER' => 'PASSENGERS',
        ];

        $types = DB::table('price_types')
            ->whereIn('code', array_keys($required))
            ->get()
            ->keyBy('code');

        foreach ($required as $code => $quantityBasis) {
            $type = $types->get($code);

            if ($type === null) {
                throw new RuntimeException("No existe el tipo de precio {$code}.");
            }

            DB::table('price_types')
                ->where('id', $type->id)
                ->update([
                    'quantity_basis' => $quantityBasis,
                    'active' => true,
                    'updated_at' => now(),
                ]);
        }

        return $types
            ->map(fn ($type): int => (int) $type->id)
            ->all();
    }

    /** @return array<string, int> */
    private function passengerTypeIds(): array
    {
        $types = DB::table('passenger_types')
            ->whereIn('code', ['ADT', 'CHD'])
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (!isset($types['ADT'], $types['CHD'])) {
            throw new RuntimeException('No existen los tipos de pasajero ADT y CHD.');
        }

        return $types;
    }

    private function provider(int $documentTypeId): int
    {
        $existing = DB::table('providers')->where('code', 'DEMO-PRICING')->first();

        DB::table('providers')->updateOrInsert(
            ['code' => 'DEMO-PRICING'],
            [
                'uuid' => $existing?->uuid ?? (string) Str::uuid(),
                'business_name' => 'Operador Turístico Demo Pricing S.A.C.',
                'commercial_name' => 'Cusco Pricing Demo',
                'document_type_id' => $documentTypeId,
                'document_number' => '20999999991',
                'tax_name' => 'Operador Turístico Demo Pricing S.A.C.',
                'email' => 'pricing.demo@example.test',
                'phone' => '+51 999 000 001',
                'website' => 'https://example.test',
                'notes' => 'Proveedor generado para pruebas de pricing y cotizaciones.',
                'active' => true,
                'deleted_at' => null,
                'created_at' => $existing?->created_at ?? now(),
                'updated_at' => now(),
            ],
        );

        return (int) DB::table('providers')
            ->where('code', 'DEMO-PRICING')
            ->value('id');
    }

    private function service(
        string $code,
        int $providerId,
        int $categoryId,
        string $name,
        string $description,
    ): int {
        $existing = DB::table('services')->where('code', $code)->first();

        DB::table('services')->updateOrInsert(
            ['code' => $code],
            [
                'uuid' => $existing?->uuid ?? (string) Str::uuid(),
                'provider_id' => $providerId,
                'service_category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'active' => true,
                'created_at' => $existing?->created_at ?? now(),
                'updated_at' => now(),
            ],
        );

        return (int) DB::table('services')->where('code', $code)->value('id');
    }

    private function variant(
        int $serviceId,
        string $code,
        string $name,
        int $minCapacity,
        int $maxCapacity,
        int $optimalCapacity,
        string $unitType,
    ): int {
        DB::table('service_variants')->updateOrInsert(
            ['code' => $code],
            [
                'service_id' => $serviceId,
                'name' => $name,
                'min_capacity' => $minCapacity,
                'max_capacity' => $maxCapacity,
                'optimal_capacity' => $optimalCapacity,
                'unit_type' => $unitType,
                'duration' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        return (int) DB::table('service_variants')->where('code', $code)->value('id');
    }

    /**
     * @param array<string, int> $variants
     * @param array<string, int> $priceTypes
     * @param array<string, int> $passengerTypes
     */
    private function insertPrices(
        array $variants,
        array $priceTypes,
        array $passengerTypes,
        int $currencyId,
    ): void {
        $rows = [
            // Hotel: importe por habitación; las noches se aplican en AccommodationCalculator.
            ['D-SGL', 'ROOM', null, null, null, 55, 75],
            ['D-DBL', 'ROOM', null, null, null, 85, 115],
            ['D-TPL', 'ROOM', null, null, null, 120, 160],

            // Transporte: rango por pasajeros asignados a cada vehículo.
            ['D-AUTO', 'GROUP', null, 1, 3, 70, 90],
            ['D-VAN', 'GROUP', null, 1, 4, 90, 120],
            ['D-VAN', 'GROUP', null, 5, 7, 120, 150],
            ['D-VAN', 'GROUP', null, 8, 10, 145, 180],
            ['D-BUS30', 'GROUP', null, 11, 20, 400, 500],
            ['D-BUS30', 'GROUP', null, 21, 30, 400, 500],

            // Entrada: importe individual según tipo de pasajero.
            ['D-ADT', 'PASSENGER', 'ADT', null, null, 45, 60],
            ['D-CHD', 'PASSENGER', 'CHD', null, null, 25, 35],
        ];

        foreach ($rows as [$variant, $priceType, $passengerType, $min, $max, $cost, $sale]) {
            DB::table('prices')->insert([
                'service_variant_id' => $variants[$variant],
                'price_type_id' => $priceTypes[$priceType],
                'passenger_type_id' => $passengerType === null
                    ? null
                    : $passengerTypes[$passengerType],
                'currency_id' => $currencyId,
                'min_quantity' => $min,
                'max_quantity' => $max,
                'valid_from' => null,
                'valid_to' => null,
                'cost' => $cost,
                'sale_price' => $sale,
                'priority' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
