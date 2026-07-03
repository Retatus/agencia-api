<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [

            [
                'code' => 'SER0001',
                'provider_id' => 1,
                'service_categories_id' => 1,
                'name' => 'Hotel Casa Andina Cusco',
                'description' => 'Hotel Casa Andina Cusco',
            ],

            [
                'code' => 'SER0002',
                'provider_id' => 2,
                'service_categories_id' => 2,
                'name' => 'Transporte Aeropuerto - Hotel',
                'description' => 'Transporte Aeropuerto - Hotel',
            ],

            [
                'code' => 'SER0003',
                'provider_id' => 3,
                'service_categories_id' => 3,
                'name' => 'Restaurante Tunupa',
                'description' => 'Restaurante Tunupa',
            ],

            [
                'code' => 'SER0004',
                'provider_id' => 4,
                'service_categories_id' => 4,
                'name' => 'Entrada Machu Picchu',
                'description' => 'Entrada Machu Picchu',
            ],

            [
                'code' => 'SER0005',
                'provider_id' => 5,
                'service_categories_id' => 5,
                'name' => 'Guía Español',
                'description' => 'Guía Español',
            ],

            [
                'code' => 'SER0006',
                'provider_id' => 6,
                'service_categories_id' => 6,
                'name' => 'City Tour Cusco',
                'description' => 'City Tour Cusco',
            ],

            [
                'code' => 'SER0007',
                'provider_id' => 7,
                'service_categories_id' => 7,
                'name' => 'Seguro de Viaje',
                'description' => 'Seguro de Viaje',
            ],

            [
                'code' => 'SER0008',
                'provider_id' => 8,
                'service_categories_id' => 8,
                'name' => 'Servicio General',
                'description' => 'Servicio General',
            ],

        ];

        foreach ($services as $service) {

            DB::table('services')->updateOrInsert(

                ['code' => $service['code']],

                [

                    'provider_id' => $service['provider_id'],
                    'service_categories_id' => $service['service_categories_id'],
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),

                ]
            );
        }
    }
}