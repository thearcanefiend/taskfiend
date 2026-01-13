<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any NULL creator_id values to user ID 1
        DB::table('tasks')
            ->whereNull('creator_id')
            ->update(['creator_id' => 1]);

        // Then ensure the column is NOT NULL and has a foreign key constraint
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('creator_id')
                ->nullable(false)
                ->change()
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make creator_id nullable again
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('creator_id')
                ->nullable()
                ->change();
        });
    }
};
