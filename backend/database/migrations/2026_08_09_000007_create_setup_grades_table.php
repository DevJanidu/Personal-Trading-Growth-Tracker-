<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setup_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->decimal('score_min', 10, 4)->nullable();
            $table->decimal('score_max', 10, 4)->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'archived'])->default('active');

            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_grades');
    }
};
