<?php

namespace Database\Factories;

use App\Models\Strategy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Strategy>
 */
class StrategyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'description' => fake()->paragraph(),
            'status' => 'active',
            'minimum_rr' => fake()->randomFloat(2, 1, 5),
            'maximum_risk_percent' => fake()->randomFloat(2, 0.25, 2),
        ];
    }
}
