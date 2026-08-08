<?php

namespace Database\Factories;

use App\Models\Strategy;
use App\Models\StrategySetup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StrategySetup>
 */
class StrategySetupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'strategy_id' => Strategy::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
