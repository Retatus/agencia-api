<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {

            $table->index(
                [
                    'service_variant_id',
                    'price_type_id',
                    'passenger_type_id',
                    'currency_id',
                    'active',
                ],
                'idx_price_lookup'
            );

            $table->index(
                ['valid_from', 'valid_to'],
                'idx_price_validity'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {

            $table->dropIndex('idx_price_lookup');
            $table->dropIndex('idx_price_validity');

        });
    }
};
