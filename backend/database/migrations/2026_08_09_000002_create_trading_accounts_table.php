<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->enum('account_type', [
                'personal_live', 'demo', 'funded', 'prop_evaluation',
                'prop_funded', 'backtesting', 'custom',
            ]);
            $table->string('broker')->nullable();
            $table->char('currency', 3);

            $table->decimal('initial_balance', 20, 4);
            $table->decimal('current_balance', 20, 4);
            $table->decimal('current_equity', 20, 4);

            $table->enum('status', ['active', 'archived', 'closed'])->default('active');

            $table->decimal('max_overall_drawdown_percent', 10, 4)->nullable();
            $table->decimal('max_daily_drawdown_percent', 10, 4)->nullable();
            $table->decimal('profit_target_percent', 10, 4)->nullable();
            $table->smallInteger('minimum_trading_days')->nullable();
            $table->smallInteger('maximum_trading_days')->nullable();
            $table->decimal('payout_target', 20, 4)->nullable();
            $table->decimal('consistency_rule_percent', 10, 4)->nullable();
            $table->enum('drawdown_calculation_type', ['balance_based', 'equity_based', 'trailing'])->nullable();
            $table->time('daily_reset_time')->nullable();
            $table->string('daily_reset_timezone', 64)->nullable();
            $table->string('challenge_phase', 64)->nullable();

            $table->date('account_created_date');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_accounts');
    }
};
