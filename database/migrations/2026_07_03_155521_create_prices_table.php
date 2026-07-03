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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')
                ->constrained('price_lists')
                ->cascadeOnDelete();

            $table->foreignId('service_variant_id')
                ->constrained('service_variants')
                ->cascadeOnDelete();

            $table->foreignId('price_type_id')
                ->constrained('price_types')
                ->cascadeOnDelete();

            $table->foreignId('passenger_type_id')
                ->nullable() // primero                
                ->constrained('passenger_types')
                ->cascadeOnDelete();

            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('cost',12,2);
            $table->decimal('sale_price',12,2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price');
    }
};
