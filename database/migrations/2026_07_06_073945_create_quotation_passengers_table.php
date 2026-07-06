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
        Schema::create('quotation_passengers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignId('passenger_type_id')
                ->constrained('passenger_types')
                ->restrictOnDelete();

            $table->string('first_name', 100);

            $table->string('last_name', 100);

            $table->date('birth_date')->nullable();

            $table->string('document_number', 30)->nullable();

            $table->string('nationality', 80)->nullable();

            $table->string('email', 150)->nullable();

            $table->string('phone', 30)->nullable();

            $table->timestamps();

            $table->index([
                'quotation_id',
                'passenger_type_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_passengers');
    }
};
