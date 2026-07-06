<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->restrictOnDelete();

            $table->string('document_number', 30);

            $table->string('first_name', 100);

            $table->string('last_name', 100);

            $table->date('birth_date')->nullable();

            $table->enum('gender', [
                'M',
                'F',
                'O'
            ])->nullable();

            $table->string('nationality', 80)->nullable();

            $table->string('email', 150)->nullable();

            $table->string('phone', 30)->nullable();

            $table->string('address', 255)->nullable();

            $table->string('city', 100)->nullable();

            $table->string('country', 100)->nullable();

            $table->text('notes')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'document_type_id',
                'document_number'
            ]);

            $table->index('last_name');
            $table->index('email');
            $table->index('phone');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};