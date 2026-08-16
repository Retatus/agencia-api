<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_prices', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Service Variant
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('service_variant_id')
                ->unique()
                ->constrained('service_variants')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('currency_id')
                ->constrained('currencies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Prices
            |--------------------------------------------------------------------------
            */

            $table->decimal('cost', 12, 2)->default(0);

            $table->decimal(
                'sale_price',
                12,
                2
            );

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
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index([
                'service_variant_id',
                'active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'base_prices'
        );
    }
};