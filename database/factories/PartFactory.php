<?php

namespace Database\Factories;

use App\Enums\PartType;
use App\Models\Part;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(PartType::cases()),
        ];
    }

    public function topic(string $title): static
    {
        return $this->state(fn () => ['title' => $title, 'type' => PartType::Topic]);
    }

    public function nusqa(string $title): static
    {
        return $this->state(fn () => ['title' => $title, 'type' => PartType::Nusqa]);
    }
}
