<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'uuid' => Str::uuid(),
                'code' => '001',
                'business_name' => 'Proveedor 1',
                'commercial_name' => 'Proveedor 1',
                'document_type_id' => 1,
                'document_number' => '12345678',
                'tax_name' => 'Proveedor 1',
                'email' => 'BtjEa@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor1.com',
                'notes' => 'Proveedor 1',
                'active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'code' => '002',
                'business_name' => 'Proveedor 2',
                'commercial_name' => 'Proveedor 2',
                'document_type_id' => 2,
                'document_number' => '12345678901',
                'tax_name' => 'Proveedor 2',
                'email' => 'g7OuM@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor2.com',
                'notes' => 'Proveedor 2',
                'active' => true,
            ], 
            [
                'uuid' => Str::uuid(),
                'code' => '003',
                'business_name' => 'Proveedor 3',
                'commercial_name' => 'Proveedor 3',
                'document_type_id' => 3,
                'document_number' => '123456789',
                'tax_name' => 'Proveedor 3',
                'email' => '6jE5o@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor3.com',
                'notes' => 'Proveedor 3',
                'active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'code' => '004',
                'business_name' => 'Proveedor 4',
                'commercial_name' => 'Proveedor 4',
                'document_type_id' => 4,
                'document_number' => '12345678',
                'tax_name' => 'Proveedor 4',
                'email' => '7VUoI@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor4.com',
                'notes' => 'Proveedor 4',               
                'active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'code' => '005',
                'business_name' => 'Proveedor 5',
                'commercial_name' => 'Proveedor 5',
                'document_type_id' => 1,
                'document_number' => '12345678',
                'tax_name' => 'Proveedor 5',
                'email' => 'g7OuM@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor5.com',
                'notes' => 'Proveedor 5',
                'active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'code' => '006',
                'business_name' => 'Proveedor 6',
                'commercial_name' => 'Proveedor 6',
                'document_type_id' => 2,
                'document_number' => '12345678901',
                'tax_name' => 'Proveedor 6',
                'email' => 'g7OuM@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor6.com',
                'notes' => 'Proveedor 6',
                'active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'code' => '007',
                'business_name' => 'Proveedor 7',
                'commercial_name' => 'Proveedor 7',
                'document_type_id' => 3,
                'document_number' => '123456789',
                'tax_name' => 'Proveedor 7',
                'email' => 'g7OuM@example.com',
                'phone' => '123456789', 
                'website' => 'www.proveedor7.com',
                'notes' => 'Proveedor 7',
                'active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'code' => '008',
                'business_name' => 'Proveedor 8',
                'commercial_name' => 'Proveedor 8',
                'document_type_id' => 4,
                'document_number' => '12345678',
                'tax_name' => 'Proveedor 8',
                'email' => 'g7OuM@example.com',
                'phone' => '123456789',
                'website' => 'www.proveedor8.com',
                'notes' => 'Proveedor 8',
                'active' => true,
            ],
        ];

        foreach ($providers as $provider) {
            DB::table('providers')->updateOrInsert(
                ['code' => $provider['code']],
                [
                    'uuid' => $provider['uuid'],
                    'business_name' => $provider['business_name'],
                    'commercial_name' => $provider['commercial_name'],
                    'document_type_id' => $provider['document_type_id'],
                    'document_number' => $provider['document_number'],
                    'tax_name' => $provider['tax_name'],
                    'email' => $provider['email'],
                    'phone' => $provider['phone'],
                    'website' => $provider['website'],
                    'notes' => $provider['notes'],
                    'active' => $provider['active'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
