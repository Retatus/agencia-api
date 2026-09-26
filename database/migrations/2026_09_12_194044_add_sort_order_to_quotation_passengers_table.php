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
        Schema::table('quotation_passengers', function (Blueprint $table) {
            $table
                ->unsignedSmallInteger('sort_order')
                ->default(1)
                ->after('phone');
            $table
                ->boolean('active')
                ->default(true)
                ->after('sort_order');


            $table->index(
                ['quotation_id', 'sort_order'],
                'quotation_passengers_quotation_sort_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_passengers', function (Blueprint $table) {
            $table->dropIndex(
                'quotation_passengers_quotation_sort_index'
            );

            $table->dropColumn([
                'sort_order',
                'active',
            ]);
        });
    }
};
