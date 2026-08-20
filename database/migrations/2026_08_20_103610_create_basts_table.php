<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basts', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('document_number', 150)
                ->nullable()
                ->unique();

            $table->unsignedInteger('sequence_number')->nullable();

            $table->string('document_code', 30)
                ->default('BAST');

            $table->unsignedTinyInteger('document_month')->nullable();

            $table->unsignedSmallInteger('document_year')->nullable();

            $table->foreignId('bast_type_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->date('document_date');

            $table->date('handover_date');

            $table->string('handover_place');

            $table->string('status', 20)
                ->default('draft')
                ->index();

            $table->timestamp('finalized_at')->nullable();

            $table->foreignId('finalized_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('completed_at')->nullable();

            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('archived_at')->nullable();

            $table->foreignId('archived_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'document_year',
                'document_month',
            ]);

            $table->index([
                'department_id',
                'status',
            ]);

            $table->index([
                'bast_type_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basts');
    }
};
