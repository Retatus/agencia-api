<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_items', function (Blueprint $table) {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Price List
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('price_list_id')
                    ->constrained('price_lists')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Variant
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'service_variant_id'
                    )
                    ->constrained(
                        'service_variants'
                    )
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Adjustment
                |--------------------------------------------------------------------------
                |
                | OVERRIDE
                | FIXED
                | PERCENTAGE
                |
                */

                $table->string(
                    'adjustment_type',
                    20
                );

                /*
                |--------------------------------------------------------------------------
                | Adjustment Value
                |--------------------------------------------------------------------------
                |
                | FIXED:
                | +30 / -20
                |
                | PERCENTAGE:
                | +20 / -10
                |
                */

                $table
                    ->decimal(
                        'adjustment_value',
                        12,
                        4
                    )
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Override
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'override_cost',
                        12,
                        2
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'override_sale_price',
                        12,
                        2
                    )
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | State
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('active')
                    ->default(true);

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Unique
                |--------------------------------------------------------------------------
                |
                | Una variante solo debería tener una regla
                | dentro de una determinada PriceList.
                |
                */

                $table->unique(
                    [
                        'price_list_id',
                        'service_variant_id',
                    ],
                    'uq_price_list_variant'
                );

                /*
                |--------------------------------------------------------------------------
                | Lookup
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'service_variant_id',
                        'active',
                    ],
                    'idx_price_list_items_lookup'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};