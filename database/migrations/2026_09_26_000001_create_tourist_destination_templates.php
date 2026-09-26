<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourist_destinations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 10)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('currency_id')->constrained('currencies');
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'name']);
        });

        Schema::create('tourist_destination_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_destination_id')
                ->constrained('tourist_destinations')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(
                ['tourist_destination_id', 'day_number'],
                'tourist_destination_day_unique'
            );
        });

        Schema::create('tourist_destination_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_destination_day_id')
                ->constrained('tourist_destination_days')
                ->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->unsignedInteger('duration')->default(1);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('estimated_price', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('tourist_destination_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('tourist_destinations')
                ->nullOnDelete();
            $table->string('tourist_destination_name', 150)
                ->nullable()
                ->after('tourist_destination_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tourist_destination_id');
            $table->dropColumn('tourist_destination_name');
        });

        Schema::dropIfExists('tourist_destination_items');
        Schema::dropIfExists('tourist_destination_days');
        Schema::dropIfExists('tourist_destinations');
    }
};
