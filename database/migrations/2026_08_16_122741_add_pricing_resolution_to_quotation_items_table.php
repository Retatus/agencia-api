<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Base Price
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'base_price_id'
                    )
                    ->nullable()
                    ->after(
                        'service_variant_id'
                    )
                    ->constrained(
                        'base_prices'
                    )
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Price List
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'price_list_id'
                    )
                    ->nullable()
                    ->after(
                        'base_price_id'
                    )
                    ->constrained(
                        'price_lists'
                    )
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Price List Item
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'price_list_item_id'
                    )
                    ->nullable()
                    ->after(
                        'price_list_id'
                    )
                    ->constrained(
                        'price_list_items'
                    )
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Pricing Source
                |--------------------------------------------------------------------------
                |
                | BASE
                | PRICE_LIST
                | MANUAL
                |
                */

                $table
                    ->string(
                        'pricing_source',
                        30
                    )
                    ->default('BASE')
                    ->after(
                        'price_list_item_id'
                    );

                /*
                |--------------------------------------------------------------------------
                | Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'base_cost',
                        12,
                        2
                    )
                    ->nullable()
                    ->after(
                        'pricing_source'
                    );

                $table
                    ->decimal(
                        'base_price',
                        12,
                        2
                    )
                    ->nullable()
                    ->after(
                        'base_cost'
                    );

                $table
                    ->string(
                        'adjustment_type',
                        20
                    )
                    ->nullable()
                    ->after(
                        'base_price'
                    );

                $table
                    ->decimal(
                        'adjustment_value',
                        12,
                        4
                    )
                    ->nullable()
                    ->after(
                        'adjustment_type'
                    );

                /*
                |--------------------------------------------------------------------------
                | Index
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'price_list_id',
                    'service_variant_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'quotation_items',
            function (Blueprint $table) {

                $table->dropForeign([
                    'base_price_id',
                ]);

                $table->dropForeign([
                    'price_list_id',
                ]);

                $table->dropForeign([
                    'price_list_item_id',
                ]);

                $table->dropColumn([
                    'base_price_id',
                    'price_list_id',
                    'price_list_item_id',
                    'pricing_source',
                    'base_cost',
                    'base_price',
                    'adjustment_type',
                    'adjustment_value',
                ]);
            }
        );
    }
};