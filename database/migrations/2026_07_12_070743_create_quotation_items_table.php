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

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('quotation_itinerary_id')
                ->constrained('quotation_itineraries')
                ->cascadeOnDelete();

            // Servicio del catálogo (opcional)
            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            // Variante del servicio (opcional)
            $table->foreignId('service_variant_id')
                ->nullable()
                ->constrained('service_variants')
                ->nullOnDelete();           

            /*
            |--------------------------------------------------------------------------
            | Item Information
            |--------------------------------------------------------------------------
            */

            $table->enum('item_type', [
                'CATALOG',
                'CUSTOM'
            ])->default('CATALOG');

            // Snapshot del nombre del servicio
            $table->string('name', 200);

            // Snapshot de la variante
            $table->string('variant_name', 200)->nullable();

            // Descripción editable
            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->integer('duration')->nullable();

            $table->decimal('quantity', 10, 2)->default(1);

            $table->unsignedBigInteger('price_id')->nullable();

            $table->decimal('unit_cost', 12, 2)->default(0);

            $table->decimal('unit_price', 12, 2)->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Ordering
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort_order')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->boolean('active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Registrar cuándo se recalcularon por última vez los importes de la cotización.
            |--------------------------------------------------------------------------
            */
            $table->timestamp('calculated_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'quotation_itinerary_id',
                'sort_order'
            ]);

            $table->index('item_type');

            $table->index('service_id');

            $table->index('service_variant_id');

            $table->index('active');
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
