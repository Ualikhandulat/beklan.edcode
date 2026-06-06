<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['10А', '10Б', '11А', '11Б', '9А', '9Б']).' — '.fake()->year(),
            'description' => fake()->sentence(6),
        ];
    }
}
