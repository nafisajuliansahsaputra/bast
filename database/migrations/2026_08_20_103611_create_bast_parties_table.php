<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bast_parties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bast_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('party_type', 30)->index();

            $table->string('name');

            $table->string('nip', 50)->nullable();

            $table->string('position')->nullable();

            $table->string('department')->nullable();

            $table->string('institution');

            $table->text('address')->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'bast_id',
                'party_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bast_parties');
    }
};
