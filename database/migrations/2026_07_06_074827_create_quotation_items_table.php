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
        Schema::create('quotation_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignId('service_variant_id')
                ->constrained('service_variants')
                ->restrictOnDelete();

            $table->foreignId('price_id')
                ->constrained('prices')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot
            |--------------------------------------------------------------------------
            */

            $table->string('provider_name', 150);

            $table->string('service_name', 150);

            $table->string('variant_name', 150);

            /*
            |--------------------------------------------------------------------------
            | Operación
            |--------------------------------------------------------------------------
            */

            $table->date('service_date')->nullable();

            $table->unsignedInteger('quantity')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Snapshot de precios
            |--------------------------------------------------------------------------
            */

            $table->decimal('unit_cost', 12, 2);

            $table->decimal('unit_price', 12, 2);

            $table->decimal('total_cost', 12, 2);

            $table->decimal('total_price', 12, 2);

            /*
            |--------------------------------------------------------------------------
            | Observaciones
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')->nullable();

            $table->unsignedInteger('sort_order')->default(1);

            $table->boolean('active')->default(true);

              /*
            |--------------------------------------------------------------------------
            | Registrar cuándo se recalcularon por última vez los importes de la cotización.
            |--------------------------------------------------------------------------
            */
            $table->timestamp('calculated_at')->nullable();

            $table->timestamps();

            $table->index([
                'quotation_id',
                'service_variant_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
