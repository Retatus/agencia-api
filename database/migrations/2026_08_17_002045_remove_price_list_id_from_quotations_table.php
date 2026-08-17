<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Eliminar FK
            |--------------------------------------------------------------------------
            */

            $table->dropForeign([
                'price_list_id',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Eliminar columna
            |--------------------------------------------------------------------------
            */

            $table->dropColumn(
                'price_list_id'
            );
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {

            $table
                ->foreignId('price_list_id')
                ->nullable()
                ->constrained('price_lists')
                ->nullOnDelete();
        });
    }
};