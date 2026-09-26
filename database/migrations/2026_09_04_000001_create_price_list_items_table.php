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
            $table->foreignId('price_list_id')
                ->constrained('price_lists')
                ->cascadeOnDelete();
            $table->foreignId('price_id')
                ->constrained('prices')
                ->cascadeOnDelete();
            $table->enum('adjustment_type', [
                'PERCENTAGE',
                'FIXED',
                'OVERRIDE',
            ]);
            $table->decimal('cost_adjustment', 12, 2)->nullable();
            $table->decimal('sale_adjustment', 12, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(
                ['price_list_id', 'price_id'],
                'price_list_items_list_price_unique'
            );
            $table->index(
                ['price_id', 'active'],
                'price_list_items_price_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};
