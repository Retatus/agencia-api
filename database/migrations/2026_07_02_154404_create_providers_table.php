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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 20)->unique();
            $table->string('business_name', 200);
            $table->string('commercial_name', 200);
            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->casacadeOnDelete();
            $table->string('document_number', 30);
            $table->string('tax_name', 200);
            $table->string('email', 150);
            $table->string('phone', 50);
            $table->string('website', 200);
            $table->text('notes');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
