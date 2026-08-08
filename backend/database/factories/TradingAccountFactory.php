<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\TradingAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TradingAccount>
 */
class TradingAccountFactory extends Factory
{
    public function definition(): array
    {
        $initialBalance = fake()->randomFloat(2, 1000, 50000);

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true).' Account',
            'account_type' => fake()->randomElement(AccountType::cases())->value,
            'broker' => fake()->company(),
            'currency' => 'USD',
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'current_equity' => $initialBalance,
            'status' => 'active',
            'account_created_date' => fake()->date(),
            'notes' => null,
        ];
    }

    public function funded(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Funded->value,
            'max_overall_drawdown_percent' => 10,
            'max_daily_drawdown_percent' => 5,
            'profit_target_percent' => 8,
        ]);
    }
}
