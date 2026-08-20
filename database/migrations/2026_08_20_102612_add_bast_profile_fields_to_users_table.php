<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 50)
                ->nullable()
                ->unique()
                ->after('name');

            $table->string('position')
                ->nullable()
                ->after('email');

            $table->string('phone', 30)
                ->nullable()
                ->after('position');

            $table->string('status', 20)
                ->default('active')
                ->index()
                ->after('phone');

            $table->foreignId('role_id')
                ->nullable()
                ->after('status')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->after('role_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['role_id']);
            $table->dropUnique(['nip']);

            $table->dropColumn([
                'nip',
                'position',
                'phone',
                'status',
                'role_id',
                'department_id',
            ]);
        });
    }
};
