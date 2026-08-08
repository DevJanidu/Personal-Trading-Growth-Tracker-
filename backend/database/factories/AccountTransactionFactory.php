<?php

namespace Database\Factories;

use App\Models\AccountTransaction;
use App\Models\TradingAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountTransaction>
 */
class AccountTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trading_account_id' => TradingAccount::factory(),
            'type' => 'deposit',
            'amount' => fake()->randomFloat(2, 100, 5000),
            'transaction_date' => fake()->date(),
            'notes' => null,
        ];
    }
}
