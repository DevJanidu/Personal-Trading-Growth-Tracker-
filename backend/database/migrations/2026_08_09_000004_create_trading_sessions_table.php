<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->time('start_time_utc')->nullable();
            $table->time('end_time_utc')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_sessions');
    }
};
