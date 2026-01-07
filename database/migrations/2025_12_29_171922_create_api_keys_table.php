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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_hash')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at');
            $table->timestamp('invalidated_at')->nullable();

            $table->index('key_hash');
            $table->index(['user_id', 'invalidated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
