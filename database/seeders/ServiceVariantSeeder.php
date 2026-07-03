<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = [

            // HOTEL

            ['service_id'=>1,'code'=>'SGL','name'=>'Habitación Simple','min_capacity'=>1,'max_capacity'=>1,'optimal_capacity'=>1,'unit_type'=>'ROOM'],

            ['service_id'=>1,'code'=>'DBL','name'=>'Habitación Doble','min_capacity'=>2,'max_capacity'=>2,'optimal_capacity'=>2,'unit_type'=>'ROOM'],

            ['service_id'=>1,'code'=>'TPL','name'=>'Habitación Triple','min_capacity'=>3,'max_capacity'=>3,'optimal_capacity'=>3,'unit_type'=>'ROOM'],

            // TRANSPORTE

            ['service_id'=>2,'code'=>'AUTO','name'=>'Auto','min_capacity'=>1,'max_capacity'=>3,'optimal_capacity'=>3,'unit_type'=>'VEHICLE'],

            ['service_id'=>2,'code'=>'VAN','name'=>'Van','min_capacity'=>4,'max_capacity'=>10,'optimal_capacity'=>10,'unit_type'=>'VEHICLE'],

            ['service_id'=>2,'code'=>'BUS30','name'=>'Bus 30','min_capacity'=>11,'max_capacity'=>30,'optimal_capacity'=>30,'unit_type'=>'VEHICLE'],

            ['service_id'=>2,'code'=>'BUS45','name'=>'Bus 45','min_capacity'=>31,'max_capacity'=>45,'optimal_capacity'=>45,'unit_type'=>'VEHICLE'],

            // RESTAURANTE

            ['service_id'=>3,'code'=>'MENU','name'=>'Menú Turístico','min_capacity'=>1,'max_capacity'=>100,'optimal_capacity'=>20,'unit_type'=>'PERSON'],

            ['service_id'=>3,'code'=>'BUFFET','name'=>'Buffet','min_capacity'=>15,'max_capacity'=>300,'optimal_capacity'=>80,'unit_type'=>'GROUP'],

            // TICKET

            ['service_id'=>4,'code'=>'ADULT','name'=>'Adulto','min_capacity'=>1,'max_capacity'=>1,'optimal_capacity'=>1,'unit_type'=>'PERSON'],

            ['service_id'=>4,'code'=>'CHILD','name'=>'Niño','min_capacity'=>1,'max_capacity'=>1,'optimal_capacity'=>1,'unit_type'=>'PERSON'],

            ['service_id'=>4,'code'=>'STUDENT','name'=>'Estudiante','min_capacity'=>1,'max_capacity'=>1,'optimal_capacity'=>1,'unit_type'=>'PERSON'],

            // GUIA

            ['service_id'=>5,'code'=>'GROUP','name'=>'Guía por Grupo','min_capacity'=>1,'max_capacity'=>25,'optimal_capacity'=>20,'unit_type'=>'GROUP'],

            // ACTIVIDAD

            ['service_id'=>6,'code'=>'REGULAR','name'=>'Servicio Regular','min_capacity'=>1,'max_capacity'=>30,'optimal_capacity'=>15,'unit_type'=>'GROUP'],

            // SEGURO

            ['service_id'=>7,'code'=>'IND','name'=>'Seguro Individual','min_capacity'=>1,'max_capacity'=>1,'optimal_capacity'=>1,'unit_type'=>'PERSON'],

            // GENERAL database/seeders/ServiceVariantSeeder.php

            ['service_id'=>8,'code'=>'STD','name'=>'Servicio Estándar','min_capacity'=>1,'max_capacity'=>999,'optimal_capacity'=>1,'unit_type'=>'UNIT'],

        ];

        foreach ($variants as $variant){

            DB::table('service_variants')->updateOrInsert(

                [
                    'service_id'=>$variant['service_id'],
                    'code'=>$variant['code']
                ],

                [

                    'name'=>$variant['name'],
                    'min_capacity'=>$variant['min_capacity'],
                    'max_capacity'=>$variant['max_capacity'],
                    'optimal_capacity'=>$variant['optimal_capacity'],
                    'unit_type'=>$variant['unit_type'],
                    'active'=>true,
                    'updated_at'=>now(),
                    'created_at'=>now()

                ]

            );

        }
    }
}