<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'image' => null,
            'is_ent_subject' => true,
            'is_active' => true,
        ];
    }

    public function entSubject(string $title): static
    {
        return $this->state(fn () => ['title' => $title, 'is_ent_subject' => true]);
    }
}
