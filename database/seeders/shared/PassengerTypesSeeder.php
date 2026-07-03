<?php

namespace Database\Seeders\Shared;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassengerTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [

            [
                'code' => 'ADT',
                'name' => 'Adulto',
                'description' => 'Pasajero adulto'
            ],

            [
                'code' => 'CHD',
                'name' => 'Niño',
                'description' => 'Pasajero niño'
            ],

            [
                'code' => 'STD',
                'name' => 'Estudiante',
                'description' => 'Pasajero estudiante'
            ],

            [
                'code' => 'INF',
                'name' => 'Infante',
                'description' => 'Pasajero infante'
            ]

        ];

        foreach ($rows as $row) {

            DB::table('passenger_types')->updateOrInsert(

                ['code' => $row['code']],

                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
