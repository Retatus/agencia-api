<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AndesTestTravelSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $documentTypeId = $this->requiredId('document_types', 'RUC');
            $currencyId = $this->requiredId('currencies', 'USD');
            $priceTypes = $this->priceTypes();
            $passengerTypes = [
                'ADT' => $this->requiredId('passenger_types', 'ADT'),
                'CHD' => $this->requiredId('passenger_types', 'CHD'),
            ];

            $providerId = $this->provider($documentTypeId);

            $services = [
                'HOTEL' => $this->service($providerId, 'AT-HOTEL', 'HOTEL', 'Hotel Demo Cusco', 'Alojamiento para pruebas de recomendaciones.'),
                'TRANSPORT' => $this->service($providerId, 'AT-TRANS', 'TRANSPORT', 'Transporte Demo Cusco', 'Flota para pruebas por capacidad y ocupación.'),
                'INSURANCE' => $this->service($providerId, 'AT-SEGURO', 'INSURANCE', 'Seguro de Viaje Demo', 'Seguro individual para adultos y niños.'),
                'ACTIVITY' => $this->service($providerId, 'AT-CITY', 'ACTIVITY', 'City Tour Demo', 'Actividad privada con tarifa por tamaño de grupo.'),
                'GUIDE' => $this->service($providerId, 'AT-GUIA', 'GUIDE', 'Guía Turístico Demo', 'Guía privado con tarifa por tamaño de grupo.'),
                'TRANSFER' => $this->service($providerId, 'AT-TRAS', 'TRANSPORT', 'Traslado Demo', 'Traslado privado con tarifa según pasajeros.'),
            ];

            $variants = [
                'AT-SGL' => $this->variant($services['HOTEL'], 'AT-SGL', 'Habitación Simple', 1, 1, 1, 'ROOM'),
                'AT-DBL' => $this->variant($services['HOTEL'], 'AT-DBL', 'Habitación Doble', 2, 2, 2, 'ROOM'),
                'AT-TPL' => $this->variant($services['HOTEL'], 'AT-TPL', 'Habitación Triple', 3, 3, 3, 'ROOM'),

                'AT-AUTO' => $this->variant($services['TRANSPORT'], 'AT-AUTO', 'Auto', 1, 3, 3, 'VEHICLE'),
                'AT-VAN' => $this->variant($services['TRANSPORT'], 'AT-VAN', 'Van', 4, 10, 10, 'VEHICLE'),
                'AT-MINI' => $this->variant($services['TRANSPORT'], 'AT-MINI', 'Minibús', 11, 20, 20, 'VEHICLE'),

                'AT-SEGIND' => $this->variant($services['INSURANCE'], 'AT-SEGIND', 'Seguro Individual', 1, 1, 1, 'PERSON'),
                'AT-CITYR' => $this->variant($services['ACTIVITY'], 'AT-CITYR', 'City Tour Regular', 1, 30, 15, 'GROUP'),
                'AT-GUIAPR' => $this->variant($services['GUIDE'], 'AT-GUIAPR', 'Guía Privado', 1, 30, 20, 'GROUP'),
                'AT-TRASPR' => $this->variant($services['TRANSFER'], 'AT-TRASPR', 'Traslado Privado', 1, 10, 10, 'VEHICLE'),
            ];

            DB::table('prices')->whereIn('service_variant_id', array_values($variants))->delete();

            $prices = [
                // variant, price type, passenger type, min, max, cost, sale
                ['AT-SGL', 'ROOM', null, null, null, 55, 75],
                ['AT-DBL', 'ROOM', null, null, null, 85, 115],
                ['AT-TPL', 'ROOM', null, null, null, 120, 160],

                ['AT-AUTO', 'GROUP', null, 1, 3, 70, 90],
                ['AT-VAN', 'GROUP', null, 1, 4, 90, 120],
                ['AT-VAN', 'GROUP', null, 5, 7, 120, 150],
                ['AT-VAN', 'GROUP', null, 8, 10, 145, 180],
                ['AT-MINI', 'GROUP', null, 11, 15, 250, 320],
                ['AT-MINI', 'GROUP', null, 16, 20, 300, 390],

                ['AT-SEGIND', 'PASSENGER', 'ADT', null, null, 8, 12],
                ['AT-SEGIND', 'PASSENGER', 'CHD', null, null, 6, 9],

                ['AT-CITYR', 'GROUP', null, 1, 10, 180, 240],
                ['AT-CITYR', 'GROUP', null, 11, 20, 240, 320],
                ['AT-CITYR', 'GROUP', null, 21, 30, 300, 400],

                ['AT-GUIAPR', 'GROUP', null, 1, 10, 90, 130],
                ['AT-GUIAPR', 'GROUP', null, 11, 20, 120, 170],
                ['AT-GUIAPR', 'GROUP', null, 21, 30, 150, 210],

                ['AT-TRASPR', 'GROUP', null, 1, 4, 65, 90],
                ['AT-TRASPR', 'GROUP', null, 5, 7, 85, 115],
                ['AT-TRASPR', 'GROUP', null, 8, 10, 105, 140],
            ];

            foreach ($prices as [$variant, $type, $passenger, $min, $max, $cost, $sale]) {
                DB::table('prices')->insert([
                    'service_variant_id' => $variants[$variant],
                    'price_type_id' => $priceTypes[$type],
                    'passenger_type_id' => $passenger === null ? null : $passengerTypes[$passenger],
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
        });
    }

    /** @return array<string, int> */
    private function priceTypes(): array
    {
        $bases = ['ROOM' => 'UNITS', 'GROUP' => 'PASSENGERS', 'PASSENGER' => 'PASSENGERS'];
        $ids = [];

        foreach ($bases as $code => $basis) {
            $id = $this->requiredId('price_types', $code);
            DB::table('price_types')->where('id', $id)->update([
                'quantity_basis' => $basis,
                'active' => true,
                'updated_at' => now(),
            ]);
            $ids[$code] = $id;
        }

        return $ids;
    }

    private function provider(int $documentTypeId): int
    {
        $current = DB::table('providers')->where('code', 'ANDTEST01')->first();

        DB::table('providers')->updateOrInsert(['code' => 'ANDTEST01'], [
            'uuid' => $current?->uuid ?? (string) Str::uuid(),
            'business_name' => 'Andes Test Travel S.A.C.',
            'commercial_name' => 'Andes Test Travel',
            'document_type_id' => $documentTypeId,
            'document_number' => '20609999991',
            'tax_name' => 'Andes Test Travel S.A.C.',
            'email' => 'reservas@andestest.local',
            'phone' => '+51 984 000 001',
            'website' => 'https://andestest.local',
            'notes' => 'Proveedor para pruebas de Pricing y Quotation.',
            'active' => true,
            'deleted_at' => null,
            'created_at' => $current?->created_at ?? now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('providers')->where('code', 'ANDTEST01')->value('id');
    }

    private function service(int $providerId, string $code, string $category, string $name, string $description): int
    {
        $categoryId = $this->requiredId('service_categories', $category);
        $current = DB::table('services')->where('code', $code)->first();

        DB::table('services')->updateOrInsert(['code' => $code], [
            'uuid' => $current?->uuid ?? (string) Str::uuid(),
            'provider_id' => $providerId,
            'service_category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'active' => true,
            'created_at' => $current?->created_at ?? now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('services')->where('code', $code)->value('id');
    }

    private function variant(int $serviceId, string $code, string $name, int $min, int $max, int $optimal, string $unitType): int
    {
        DB::table('service_variants')->updateOrInsert(['code' => $code], [
            'service_id' => $serviceId,
            'name' => $name,
            'min_capacity' => $min,
            'max_capacity' => $max,
            'optimal_capacity' => $optimal,
            'unit_type' => $unitType,
            'duration' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('service_variants')->where('code', $code)->value('id');
    }

    private function requiredId(string $table, string $code): int
    {
        $id = (int) DB::table($table)->where('code', $code)->value('id');

        if ($id === 0) {
            throw new RuntimeException("No existe {$table}.code={$code}. Ejecuta primero DatabaseSeeder.");
        }

        return $id;
    }
}
