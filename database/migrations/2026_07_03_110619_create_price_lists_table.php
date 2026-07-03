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
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('code',30)->unique();
            $table->string('name',150);
            $table->text('description')->nullable();
            $table->foreignId('currency_id')
                ->constrained('currencies');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->unsignedInteger('priority')->default(1);
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
