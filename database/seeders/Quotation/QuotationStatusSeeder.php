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
                'name' => 'Borrador',
                'description' => 'La cotización se está editando y aún no está lista.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'READY',
                'name' => 'Lista',
                'description' => 'La cotización fue validada y puede enviarse.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SENT',
                'name' => 'Enviada',
                'description' => 'La cotización ha sido enviada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'REJECTED',
                'name' => 'Rechazada',
                'description' => 'La cotización ha sido rechazada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EXPIRED',
                'name' => 'Vencida',
                'description' => 'La cotización ha expirado.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CONFIRMED',
                'name' => 'Confirmada',
                'description' => 'La cotización ha sido confirmada.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CANCELLED',
                'name' => 'Cancelada',
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

        DB::table('quotation_statuses')
            ->whereIn('code', ['PENDING', 'APPROVED'])
            ->update([
                'active' => false,
                'updated_at' => now(),
            ]);
    }
}
