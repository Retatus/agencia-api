<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreignId('price_list_id')
                ->nullable()
                ->after('price_id')
                ->constrained('price_lists')
                ->nullOnDelete();
            $table->foreignId('price_list_item_id')
                ->nullable()
                ->after('price_list_id')
                ->constrained('price_list_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('price_list_item_id');
            $table->dropConstrainedForeignId('price_list_id');
        });
    }
};
