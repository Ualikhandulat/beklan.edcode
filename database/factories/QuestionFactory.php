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

    /**
     * Формирует узнаваемый текст вопроса вида "Вопрос для {Предмет}, №{id}. {lorem}".
     * Позволяет демо-контенту оставаться прослеживаемым по предмету/id и при этом показывать заказчику настоящий lorem-текст.
     */
    private function questionText(Question $q, string $label = 'Вопрос'): string
    {
        $subjectTitle = $q->subject?->title ?? 'Предмет';

        return "{$label} для {$subjectTitle}, №{$q->id}. ".fake()->sentence(6).'?';
    }

    /** "Вариант N (правильный)" / "(неправильный)" — сразу видно правильный вариант в демо */
    private function variantLabel(int $index, bool $correct, ?string $note = null): string
    {
        $status = $correct ? 'правильный' : 'неправильный';
        $suffix = $note ? " {$note}" : '';

        return "Вариант {$index} ({$status}{$suffix})";
    }

    /** Вопрос с одним правильным ответом */
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
                'question' => $this->questionText($q),
                'answers' => [0],
                'var1' => $this->variantLabel(1, true),
                'var2' => $this->variantLabel(2, false),
                'var3' => $this->variantLabel(3, false),
                'var4' => $this->variantLabel(4, false),
            ]);
        });
    }

    /** Вопрос с несколькими правильными ответами */
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
                'question' => $this->questionText($q),
                'answers' => [0, 1],
                'var1' => $this->variantLabel(1, true),
                'var2' => $this->variantLabel(2, true),
                'var3' => $this->variantLabel(3, false),
                'var4' => $this->variantLabel(4, false),
                'var5' => $this->variantLabel(5, false),
                'var6' => $this->variantLabel(6, false),
            ]);
        });
    }

    /**
     * Вопрос на соответствие.
     *
     * Формат: var1/var2 — вопросы левого столбца, var5 — правильный ответ для var1,
     * var6 — правильный ответ для var2, var7/var8 — дистракторы. После сжатия vars
     * получается [var1, var2, var5, var6, var7, var8], поэтому правильные индексы — [2, 3].
     */
    public function match(int $subjectId, int $partId): static
    {
        return $this->state(fn () => [
            'subject_id' => $subjectId,
            'part_id' => $partId,
            'type' => QuestionType::IS_MATCH,
            'count_variants' => 2,
            'count_answers' => 2,
        ])->afterCreating(function (Question $q) {
            $subjectTitle = $q->subject?->title ?? 'Предмет';

            QuestionDetail::create([
                'question_id' => $q->id,
                'question' => 'Установите соответствие:',
                'answers' => [2, 3],
                'var1' => "Вопрос 1 для {$subjectTitle}, №{$q->id}",
                'var2' => "Вопрос 2 для {$subjectTitle}, №{$q->id}",
                'var5' => $this->variantLabel(1, true, 'для вопроса 1'),
                'var6' => $this->variantLabel(2, true, 'для вопроса 2'),
                'var7' => $this->variantLabel(3, false),
                'var8' => $this->variantLabel(4, false),
            ]);
        });
    }

    /** Контекстный вопрос с вопросами к контексту */
    public function group(int $subjectId, int $partId): static
    {
        return $this->state(fn () => [
            'subject_id' => $subjectId,
            'part_id' => $partId,
            'type' => QuestionType::IS_GROUP,
            'count_variants' => 0,
            'count_answers' => 0,
        ])->afterCreating(function (Question $q) {
            $subjectTitle = $q->subject?->title ?? 'Предмет';
            $q->update(['text' => "Контекст для {$subjectTitle}, №{$q->id}. ".fake()->paragraph(3)]);

            for ($i = 1; $i <= 3; $i++) {
                QuestionDetail::create([
                    'question_id' => $q->id,
                    'question' => "Вопрос {$i} к контексту {$subjectTitle}, №{$q->id}. ".fake()->sentence(6).'?',
                    'answers' => [0],
                    'var1' => $this->variantLabel(1, true),
                    'var2' => $this->variantLabel(2, false),
                    'var3' => $this->variantLabel(3, false),
                    'var4' => $this->variantLabel(4, false),
                ]);
            }
        });
    }
}
