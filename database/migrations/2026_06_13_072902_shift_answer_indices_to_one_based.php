<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Переводит индексы ответов с 0-based на 1-based (var1 = 1, var2 = 2, …).
 *
 * Затрагивает уже сохранённые данные, согласованные со старой 0-based схемой:
 *  - question_details.answers           — правильные ответы (+1 к каждому элементу);
 *  - test_subjects.questions[].var_order — порядок перемешивания вариантов (+1);
 *  - test_subjects.questions[].user_answers — ответы студента (+1, null сохраняем).
 *
 * Завершённые попытки не пересчитываются (score / is_right уже сохранены) — сдвиг
 * лишь сохраняет согласованность страниц разбора с новой 1-based схемой.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->shiftAll(+1);
    }

    public function down(): void
    {
        $this->shiftAll(-1);
    }

    private function shiftAll(int $delta): void
    {
        \App\Models\QuestionDetail::withTrashed()->chunkById(500, function ($details) use ($delta) {
            foreach ($details as $detail) {
                $answers = $detail->answers;

                if (! is_array($answers) || $answers === []) {
                    continue;
                }

                $detail->answers = array_map(fn ($v) => (int) $v + $delta, $answers);
                $detail->save();
            }
        });

        \App\Models\TestSubject::chunkById(500, function ($testSubjects) use ($delta) {
            foreach ($testSubjects as $testSubject) {
                $questions = $testSubject->questions;

                if (! is_array($questions) || $questions === []) {
                    continue;
                }

                foreach ($questions as &$q) {
                    if (! empty($q['var_order']) && is_array($q['var_order'])) {
                        $q['var_order'] = array_map(fn ($v) => (int) $v + $delta, $q['var_order']);
                    }

                    if (! empty($q['user_answers']) && is_array($q['user_answers'])) {
                        $q['user_answers'] = array_map(
                            fn ($v) => $v === null ? null : (int) $v + $delta,
                            $q['user_answers']
                        );
                    }
                }
                unset($q);

                $testSubject->questions = $questions;
                $testSubject->save();
            }
        });
    }
};
