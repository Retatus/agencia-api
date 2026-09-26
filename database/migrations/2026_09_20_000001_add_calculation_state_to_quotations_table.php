<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('calculation_status', 20)
                ->default('CURRENT')
                ->after('total');

            $table->json('calculation_dirty_reasons')
                ->nullable()
                ->after('calculation_status');

            $table->json('pending_calculation_items')
                ->nullable()
                ->after('calculation_dirty_reasons');

            $table->timestamp('calculated_at')
                ->nullable()
                ->after('pending_calculation_items');

            $table->index('calculation_status');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex(['calculation_status']);
            $table->dropColumn([
                'calculation_status',
                'calculation_dirty_reasons',
                'pending_calculation_items',
                'calculated_at',
            ]);
        });
    }
};
