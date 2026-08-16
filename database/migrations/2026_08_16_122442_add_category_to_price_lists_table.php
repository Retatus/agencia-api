<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Category
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'service_category_id'
                    )
                    ->nullable()
                    ->after('id')
                    ->constrained(
                        'service_categories'
                    )
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
                    
                /*
                |--------------------------------------------------------------------------
                | Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'service_category_id',
                        'valid_from',
                        'valid_to',
                        'active',
                    ],
                    'idx_price_lists_resolution'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {

                $table->dropIndex(
                    'idx_price_lists_resolution'
                );

                $table->dropForeign([
                    'service_category_id',
                ]);

                $table->dropColumn([
                    'service_category_id',
                    'priority',
                ]);
            }
        );
    }
};