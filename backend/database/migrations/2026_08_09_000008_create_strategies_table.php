<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug', 280);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');

            // DATABASE_SCHEMA.md specifies native Postgres text[]/bigint[] arrays for these
            // preference fields; implemented here as jsonb arrays instead (still a Postgres-native
            // type, cast to a PHP array by the model) so the columns remain portable across the
            // pgsql production target and the sqlite test environment without raw per-driver DDL.
            $table->jsonb('preferred_markets')->nullable();
            $table->jsonb('preferred_pairs')->nullable();
            $table->jsonb('preferred_sessions')->nullable();
            $table->jsonb('preferred_timeframes')->nullable();

            $table->decimal('minimum_rr', 10, 4)->nullable();
            $table->decimal('maximum_risk_percent', 10, 4)->nullable();
            $table->text('required_confirmations')->nullable();
            $table->text('invalidation_conditions')->nullable();
            $table->text('entry_model_notes')->nullable();
            $table->text('trade_management_rules')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategies');
    }
};
