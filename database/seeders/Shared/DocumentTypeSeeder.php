<?php

namespace Database\Seeders\Shared;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            ['code' => 'DNI',  'name' => 'Documento Nacional de Identidad'],
            ['code' => 'RUC',  'name' => 'Registro Único de Contribuyentes'],
            ['code' => 'CE',   'name' => 'Carné de Extranjería'],
            ['code' => 'PASS', 'name' => 'Pasaporte'],
        ];

        foreach ($documentTypes as $documentType) {
            DB::table('document_types')->updateOrInsert(
                ['code' => $documentType['code']],
                [
                    'name'       => $documentType['name'],
                    'active'     => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}