<?php

namespace Database\Seeders\Quotation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'DRAFT',
                'name' => 'Draft',
                'description' => 'La cotización se está editando y aún no está lista.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PENDING',
                'name' => 'Pending',
                'description' => 'La cotización está en espera de aprobación.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SENT',
                'name' => 'Sent',
                'description' => 'La cotización ha sido enviada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'APPROVED',
                'name' => 'Approved',
                'description' => 'La cotización ha sido aprobada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'REJECTED',
                'name' => 'Rejected',
                'description' => 'La cotización ha sido rechazada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EXPIRED',
                'name' => 'Expired',
                'description' => 'La cotización ha expirado.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CONFIRMED',
                'name' => 'Confirmed',
                'description' => 'La cotización ha sido confirmada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CANCELLED',
                'name' => 'Cancelled',
                'description' => 'La cotización ha sido cancelada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('quotation_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }
    }
}