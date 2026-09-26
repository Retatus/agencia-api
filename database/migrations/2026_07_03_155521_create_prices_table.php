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
            $table->foreignId('service_variant_id')
                ->constrained('service_variants')
                ->cascadeOnDelete();

            $table->foreignId('price_type_id')
                ->constrained('price_types')
                ->cascadeOnDelete();

            $table->foreignId('passenger_type_id')
                ->nullable()
                ->constrained('passenger_types')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->decimal('cost',12,2);
            $table->decimal('sale_price',12,2);
            $table->unsignedInteger('priority')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
