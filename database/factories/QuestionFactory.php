<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Part;
use App\Models\Question;
use App\Models\QuestionDetail;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'part_id' => Part::factory(),
            'type' => QuestionType::SELECT_ONE,
            'count_variants' => 4,
            'count_answers' => 1,
            'text' => null,
        ];
    }

    /** Single-answer question with detail */
    public function one(int $subjectId, int $partId): static
    {
        return $this->state(fn () => [
            'subject_id' => $subjectId,
            'part_id' => $partId,
            'type' => QuestionType::SELECT_ONE,
            'count_variants' => 4,
            'count_answers' => 1,
        ])->afterCreating(function (Question $q) {
            QuestionDetail::create([
                'question_id' => $q->id,
                'question' => fake()->sentence(8).'?',
                'answers' => [1],
                'var1' => fake()->sentence(3),
                'var2' => fake()->sentence(3),
                'var3' => fake()->sentence(3),
                'var4' => fake()->sentence(3),
            ]);
        });
    }

    /** Multi-answer question with detail */
    public function multi(int $subjectId, int $partId): static
    {
        return $this->state(fn () => [
            'subject_id' => $subjectId,
            'part_id' => $partId,
            'type' => QuestionType::SELECT_MULTI,
            'count_variants' => 6,
            'count_answers' => 2,
        ])->afterCreating(function (Question $q) {
            QuestionDetail::create([
                'question_id' => $q->id,
                'question' => fake()->sentence(8).'?',
                'answers' => [1, 3],
                'var1' => fake()->sentence(3),
                'var2' => fake()->sentence(3),
                'var3' => fake()->sentence(3),
                'var4' => fake()->sentence(3),
                'var5' => fake()->sentence(3),
                'var6' => fake()->sentence(3),
            ]);
        });
    }

    /** Match question with detail */
    public function match(int $subjectId, int $partId): static
    {
        return $this->state(fn () => [
            'subject_id' => $subjectId,
            'part_id' => $partId,
            'type' => QuestionType::IS_MATCH,
            'count_variants' => 2,
            'count_answers' => 2,
        ])->afterCreating(function (Question $q) {
            QuestionDetail::create([
                'question_id' => $q->id,
                'question' => 'Установите соответствие:',
                'answers' => [5, 6],
                'var1' => fake()->word(),
                'var2' => fake()->word(),
                'var5' => fake()->sentence(2),
                'var6' => fake()->sentence(2),
                'var7' => fake()->sentence(2),
                'var8' => fake()->sentence(2),
            ]);
        });
    }

    /** Context (group) question with sub-questions */
    public function group(int $subjectId, int $partId): static
    {
        return $this->state(fn () => [
            'subject_id' => $subjectId,
            'part_id' => $partId,
            'type' => QuestionType::IS_GROUP,
            'count_variants' => 0,
            'count_answers' => 0,
            'text' => fake()->paragraph(3),
        ])->afterCreating(function (Question $q) {
            for ($i = 0; $i < 3; $i++) {
                QuestionDetail::create([
                    'question_id' => $q->id,
                    'question' => fake()->sentence(7).'?',
                    'answers' => [1],
                    'var1' => fake()->sentence(3),
                    'var2' => fake()->sentence(3),
                    'var3' => fake()->sentence(3),
                    'var4' => fake()->sentence(3),
                ]);
            }
        });
    }
}
