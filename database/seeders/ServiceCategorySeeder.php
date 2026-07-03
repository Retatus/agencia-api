<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'HOTEL',      'name' => 'Hotel'],
            ['code' => 'TRANSPORT',  'name' => 'Transporte'],
            ['code' => 'RESTAURANT', 'name' => 'Restaurante'],
            ['code' => 'TICKET',     'name' => 'Ticket'],
            ['code' => 'GUIDE',      'name' => 'Guía Turístico'],
            ['code' => 'ACTIVITY',   'name' => 'Actividad'],
            ['code' => 'INSURANCE',  'name' => 'Seguro'],
            ['code' => 'GENERAL',    'name' => 'Servicio General'],
        ];

        foreach ($categories as $category) {
            DB::table('service_categories')->updateOrInsert(
                ['code' => $category['code']],
                [
                    'name'       => $category['name'],
                    'active'     => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}