<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bast_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bast_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('item_category_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->string('name');

            $table->string('code', 100)->nullable();

            $table->string('inventory_number', 150)->nullable();

            $table->string('serial_number', 150)->nullable();

            $table->decimal('quantity', 15, 2)
                ->default(1);

            $table->string('condition', 50)->nullable();

            $table->decimal('value', 18, 2)->nullable();

            $table->text('description')->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'bast_id',
                'sort_order',
            ]);

            $table->index('inventory_number');
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bast_items');
    }
};
