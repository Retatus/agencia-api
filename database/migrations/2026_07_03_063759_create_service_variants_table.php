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
        Schema::create('service_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->string('name', 150);
            $table->integer('min_capacity');
            $table->integer('max_capacity');
            $table->integer('optimal_capacity');
            $table->enum('unit_type', ['PERSON', 'ROOM', 'VEHICLE', 'GROUP', 'UNIT']);
            $table->integer('duration')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_variants');
    }
};
