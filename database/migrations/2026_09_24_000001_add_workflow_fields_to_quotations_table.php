<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->date('commercial_valid_until')->nullable()->after('valid_until');
            $table->timestamp('status_changed_at')->nullable()->after('calculated_at');
            $table->timestamp('sent_at')->nullable()->after('status_changed_at');
            $table->timestamp('confirmed_at')->nullable()->after('sent_at');
            $table->timestamp('rejected_at')->nullable()->after('confirmed_at');
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            $table->text('status_reason')->nullable()->after('cancelled_at');
        });

        $statuses = [
            'DRAFT' => ['Borrador', 'La cotización se encuentra en edición.'],
            'READY' => ['Lista', 'La cotización fue validada y puede enviarse.'],
            'SENT' => ['Enviada', 'La cotización ha sido enviada.'],
            'CONFIRMED' => ['Confirmada', 'La cotización ha sido confirmada.'],
            'REJECTED' => ['Rechazada', 'La cotización ha sido rechazada.'],
            'EXPIRED' => ['Vencida', 'La vigencia comercial de la cotización terminó.'],
            'CANCELLED' => ['Cancelada', 'La cotización ha sido cancelada.'],
        ];

        foreach ($statuses as $code => [$name, $description]) {
            DB::table('quotation_statuses')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'active' => true,
                    'updated_at' => now(),
                ],
            );
        }

        $readyId = DB::table('quotation_statuses')->where('code', 'READY')->value('id');
        $confirmedId = DB::table('quotation_statuses')->where('code', 'CONFIRMED')->value('id');
        $pendingId = DB::table('quotation_statuses')->where('code', 'PENDING')->value('id');
        $approvedId = DB::table('quotation_statuses')->where('code', 'APPROVED')->value('id');

        if ($pendingId && $readyId) {
            DB::table('quotations')
                ->where('quotation_status_id', $pendingId)
                ->update(['quotation_status_id' => $readyId]);
        }

        if ($approvedId && $confirmedId) {
            DB::table('quotations')
                ->where('quotation_status_id', $approvedId)
                ->update(['quotation_status_id' => $confirmedId]);
        }

        DB::table('quotation_statuses')
            ->whereIn('code', ['PENDING', 'APPROVED'])
            ->update(['active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'commercial_valid_until',
                'status_changed_at',
                'sent_at',
                'confirmed_at',
                'rejected_at',
                'cancelled_at',
                'status_reason',
            ]);
        });
    }
};
