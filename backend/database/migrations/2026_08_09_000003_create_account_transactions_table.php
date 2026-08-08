<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trading_account_id')->constrained('trading_accounts')->cascadeOnDelete();

            $table->enum('type', ['deposit', 'withdrawal', 'fee', 'refund', 'profit_split', 'adjustment']);
            $table->decimal('amount', 20, 4);
            $table->date('transaction_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['trading_account_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
