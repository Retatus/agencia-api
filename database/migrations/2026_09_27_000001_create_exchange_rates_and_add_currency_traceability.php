<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('rate', 18, 8);
            $table->date('effective_date');
            $table->string('source', 30)->default('MANUAL');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(
                ['from_currency_id', 'to_currency_id', 'effective_date'],
                'exchange_rates_pair_date_unique'
            );
            $table->index(
                ['from_currency_id', 'to_currency_id', 'active', 'effective_date'],
                'exchange_rates_lookup_index'
            );
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreignId('source_currency_id')
                ->nullable()
                ->after('price_list_item_id')
                ->constrained('currencies')
                ->nullOnDelete();
            $table->decimal('source_unit_cost', 12, 2)->nullable()->after('source_currency_id');
            $table->decimal('source_unit_price', 12, 2)->nullable()->after('source_unit_cost');
            $table->decimal('exchange_rate', 18, 8)->default(1)->after('source_unit_price');
            $table->date('exchange_rate_date')->nullable()->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_currency_id');
            $table->dropColumn([
                'source_unit_cost',
                'source_unit_price',
                'exchange_rate',
                'exchange_rate_date',
            ]);
        });

        Schema::dropIfExists('exchange_rates');
    }
};
