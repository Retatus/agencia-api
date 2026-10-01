<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_passengers', function (Blueprint $table) {
            $table->foreignId('document_type_id')
                ->nullable()
                ->after('passenger_type_id')
                ->constrained('document_types')
                ->nullOnDelete();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->date('payment_due_date')
                ->nullable()
                ->after('notes');

            $table->string('payment_status', 20)
                ->default('NOT_REQUIRED')
                ->after('payment_due_date');

            $table->index(
                ['payment_status', 'payment_due_date'],
                'quotation_items_payment_due_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropIndex('quotation_items_payment_due_index');
            $table->dropColumn(['payment_due_date', 'payment_status']);
        });

        Schema::table('quotation_passengers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_type_id');
        });
    }
};
