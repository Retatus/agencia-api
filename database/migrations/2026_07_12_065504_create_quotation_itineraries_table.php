<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_itineraries', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('day_number');

            $table->date('travel_date')->nullable();

            $table->string('title', 150)->nullable();

            $table->text('description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(1);

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'quotation_id',
                'day_number'
            ]);

            $table->index([
                'quotation_id',
                'sort_order'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_itineraries');
    }
};