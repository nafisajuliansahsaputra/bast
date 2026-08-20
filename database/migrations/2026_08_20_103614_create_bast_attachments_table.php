<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bast_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bast_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('category', 50)->nullable();

            $table->string('original_name');

            $table->string('stored_name');

            $table->string('disk', 50)
                ->default('local');

            $table->text('path');

            $table->string('mime_type', 150)->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index([
                'bast_id',
                'category',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bast_attachments');
    }
};
