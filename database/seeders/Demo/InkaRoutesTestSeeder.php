<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InkaRoutesTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $currencyId = $this->requiredId('currencies', 'USD');
            $documentTypeId = $this->requiredId('document_types', 'RUC');
            $passengers = [
                'ADT' => $this->requiredId('passenger_types', 'ADT'),
                'CHD' => $this->requiredId('passenger_types', 'CHD'),
            ];
            $priceTypes = $this->priceTypes();
            $providerId = $this->provider($documentTypeId);

            $services = [
                'HOTEL' => $this->service($providerId, 'IR-HOTEL', 'HOTEL', 'Inka Routes Hotel Demo', 'Segundo hotel para comparar recomendaciones y precios.'),
                'TRANS' => $this->service($providerId, 'IR-TRANS', 'TRANSPORT', 'Inka Routes Transporte Demo', 'Flota alternativa para pruebas funcionales.'),
                'SEGURO' => $this->service($providerId, 'IR-SEGURO', 'INSURANCE', 'Inka Routes Seguro Demo', 'Seguro individual alternativo.'),
                'CITY' => $this->service($providerId, 'IR-CITY', 'ACTIVITY', 'Inka Routes City Tour Demo', 'City tour regular y privado.'),
                'GUIA' => $this->service($providerId, 'IR-GUIA', 'GUIDE', 'Inka Routes Guías Demo', 'Guías privados en español e inglés.'),
                'TRAS' => $this->service($providerId, 'IR-TRAS', 'TRANSPORT', 'Inka Routes Traslado Demo', 'Traslados privado y VIP.'),
            ];

            $variants = [
                'IR-SGL' => $this->variant($services['HOTEL'], 'IR-SGL', 'Habitación Simple', 1, 1, 1, 'ROOM'),
                'IR-DBL' => $this->variant($services['HOTEL'], 'IR-DBL', 'Habitación Doble', 2, 2, 2, 'ROOM'),
                'IR-TPL' => $this->variant($services['HOTEL'], 'IR-TPL', 'Habitación Triple', 3, 3, 3, 'ROOM'),
                'IR-FAM' => $this->variant($services['HOTEL'], 'IR-FAM', 'Habitación Familiar', 4, 5, 4, 'ROOM'),

                'IR-AUTO' => $this->variant($services['TRANS'], 'IR-AUTO', 'Auto', 1, 3, 3, 'VEHICLE'),
                'IR-VAN' => $this->variant($services['TRANS'], 'IR-VAN', 'Van', 4, 10, 9, 'VEHICLE'),
                'IR-MINI' => $this->variant($services['TRANS'], 'IR-MINI', 'Minibús', 11, 20, 18, 'VEHICLE'),
                'IR-BUS' => $this->variant($services['TRANS'], 'IR-BUS', 'Bus 40', 21, 40, 35, 'VEHICLE'),

                'IR-SEGIND' => $this->variant($services['SEGURO'], 'IR-SEGIND', 'Seguro Individual', 1, 1, 1, 'PERSON'),
                'IR-CITYR' => $this->variant($services['CITY'], 'IR-CITYR', 'City Tour Regular', 1, 30, 20, 'GROUP'),
                'IR-CITYP' => $this->variant($services['CITY'], 'IR-CITYP', 'City Tour Privado', 1, 15, 10, 'GROUP'),
                'IR-GUIAES' => $this->variant($services['GUIA'], 'IR-GUIAES', 'Guía Español', 1, 30, 20, 'GROUP'),
                'IR-GUIAEN' => $this->variant($services['GUIA'], 'IR-GUIAEN', 'Guía Inglés', 1, 30, 20, 'GROUP'),
                'IR-TRASPR' => $this->variant($services['TRAS'], 'IR-TRASPR', 'Traslado Privado', 1, 10, 8, 'VEHICLE'),
                'IR-TRVIP' => $this->variant($services['TRAS'], 'IR-TRVIP', 'Traslado VIP', 1, 6, 5, 'VEHICLE'),
            ];

            DB::table('prices')->whereIn('service_variant_id', array_values($variants))->delete();

            $prices = [
                ['IR-SGL', 'ROOM', null, null, null, 60, 82],
                ['IR-DBL', 'ROOM', null, null, null, 92, 125],
                ['IR-TPL', 'ROOM', null, null, null, 128, 175],
                ['IR-FAM', 'ROOM', null, null, null, 175, 235],

                ['IR-AUTO', 'GROUP', null, 1, 3, 65, 85],
                ['IR-VAN', 'GROUP', null, 1, 4, 100, 130],
                ['IR-VAN', 'GROUP', null, 5, 7, 130, 165],
                ['IR-VAN', 'GROUP', null, 8, 10, 155, 195],
                ['IR-MINI', 'GROUP', null, 11, 15, 230, 300],
                ['IR-MINI', 'GROUP', null, 16, 20, 275, 350],
                ['IR-BUS', 'GROUP', null, 21, 30, 350, 455],
                ['IR-BUS', 'GROUP', null, 31, 40, 420, 540],

                ['IR-SEGIND', 'PASSENGER', 'ADT', null, null, 9, 14],
                ['IR-SEGIND', 'PASSENGER', 'CHD', null, null, 7, 10],

                ['IR-CITYR', 'GROUP', null, 1, 10, 165, 225],
                ['IR-CITYR', 'GROUP', null, 11, 20, 220, 295],
                ['IR-CITYR', 'GROUP', null, 21, 30, 280, 375],
                ['IR-CITYP', 'GROUP', null, 1, 5, 210, 285],
                ['IR-CITYP', 'GROUP', null, 6, 10, 270, 360],
                ['IR-CITYP', 'GROUP', null, 11, 15, 330, 440],

                ['IR-GUIAES', 'GROUP', null, 1, 10, 85, 125],
                ['IR-GUIAES', 'GROUP', null, 11, 20, 115, 165],
                ['IR-GUIAES', 'GROUP', null, 21, 30, 145, 205],
                ['IR-GUIAEN', 'GROUP', null, 1, 10, 105, 150],
                ['IR-GUIAEN', 'GROUP', null, 11, 20, 140, 195],
                ['IR-GUIAEN', 'GROUP', null, 21, 30, 175, 245],

                ['IR-TRASPR', 'GROUP', null, 1, 4, 60, 85],
                ['IR-TRASPR', 'GROUP', null, 5, 7, 80, 110],
                ['IR-TRASPR', 'GROUP', null, 8, 10, 100, 135],
                ['IR-TRVIP', 'GROUP', null, 1, 3, 95, 135],
                ['IR-TRVIP', 'GROUP', null, 4, 6, 125, 175],
            ];

            foreach ($prices as [$variant, $type, $passenger, $min, $max, $cost, $sale]) {
                DB::table('prices')->insert([
                    'service_variant_id' => $variants[$variant],
                    'price_type_id' => $priceTypes[$type],
                    'passenger_type_id' => $passenger === null ? null : $passengers[$passenger],
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
        $result = [];

        foreach ($bases as $code => $basis) {
            $id = $this->requiredId('price_types', $code);
            DB::table('price_types')->where('id', $id)->update([
                'quantity_basis' => $basis,
                'active' => true,
                'updated_at' => now(),
            ]);
            $result[$code] = $id;
        }

        return $result;
    }

    private function provider(int $documentTypeId): int
    {
        $current = DB::table('providers')->where('code', 'INKATEST1')->first();

        DB::table('providers')->updateOrInsert(['code' => 'INKATEST1'], [
            'uuid' => $current?->uuid ?? (string) Str::uuid(),
            'business_name' => 'Inka Routes Test S.A.C.',
            'commercial_name' => 'Inka Routes Test',
            'document_type_id' => $documentTypeId,
            'document_number' => '20608888881',
            'tax_name' => 'Inka Routes Test S.A.C.',
            'email' => 'reservas@inkaroutestest.local',
            'phone' => '+51 984 000 002',
            'website' => 'https://inkaroutestest.local',
            'notes' => 'Segundo proveedor para pruebas funcionales comparativas.',
            'active' => true,
            'deleted_at' => null,
            'created_at' => $current?->created_at ?? now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('providers')->where('code', 'INKATEST1')->value('id');
    }

    private function service(int $providerId, string $code, string $category, string $name, string $description): int
    {
        $current = DB::table('services')->where('code', $code)->first();
        DB::table('services')->updateOrInsert(['code' => $code], [
            'uuid' => $current?->uuid ?? (string) Str::uuid(),
            'provider_id' => $providerId,
            'service_category_id' => $this->requiredId('service_categories', $category),
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
