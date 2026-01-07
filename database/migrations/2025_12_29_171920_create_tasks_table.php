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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['incomplete', 'done', 'archived'])->default('incomplete');
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('datetime')->nullable();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recurrence_pattern')->nullable();
            $table->timestamps();

            $table->index(['datetime', 'status']);
            $table->index(['creator_id', 'status']);
            $table->index('project_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
