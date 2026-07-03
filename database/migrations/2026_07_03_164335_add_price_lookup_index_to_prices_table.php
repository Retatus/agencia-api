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
                    'price_list_id',
                    'service_variant_id',
                    'price_type_id'
                ],
                'idx_price_lookup'
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

        });
    }
};