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
        Schema::create('quotations', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('code', 30)->unique();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignId('price_list_id')
                ->constrained('price_lists')
                ->restrictOnDelete();

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->foreignId('quotation_status_id')
                ->constrained('quotation_statuses')
                ->restrictOnDelete();

            // Tipo de cambio utilizado al momento de cotizar
            $table->decimal('exchange_rate', 12, 6)->default(1);

            $table->date('travel_date')->nullable();

            $table->date('valid_until')->nullable();

            $table->text('notes')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('tax', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->softDeletes();

            $table->index('customer_id');
            $table->index('travel_date');
            $table->index('quotation_status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
