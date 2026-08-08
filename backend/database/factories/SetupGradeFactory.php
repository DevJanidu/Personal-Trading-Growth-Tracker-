<?php

namespace Database\Factories;

use App\Models\SetupGrade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SetupGrade>
 */
class SetupGradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->randomElement(['A+', 'A', 'B', 'C']),
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'status' => 'active',
        ];
    }
}
