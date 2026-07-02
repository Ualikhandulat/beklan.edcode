<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionDetail;
use App\Models\Test;

class TestReviewService
{
    /**
     * Build enriched subjects array for the test process/review views.
     * Used by the student test UI and the admin "работа над ошибками" page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function subjectsData(Test $test, bool $withCorrect = false): array
    {
        $detailIds = $test->subjects
            ->flatMap(fn ($testSubject) => collect($testSubject->questions)->pluck('detail_id'))
            ->unique();

        // withTrashed: a question/detail may have been soft-deleted by an admin after
        // the test was assembled — the student must still be able to finish/review it.
        $details = QuestionDetail::withTrashed()->whereIn('id', $detailIds)
            ->get()
            ->keyBy('id');

        // Load parent Question models separately to avoid the `question` column/relation name clash
        $questionModels = Question::withTrashed()->whereIn('id', $details->pluck('question_id')->unique())
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($test->subjects as $testSubject) {
            $questions = [];
            foreach ($testSubject->questions as $idx => $q) {
                $detail = $details->get($q['detail_id']);
                $questionModel = $detail ? $questionModels->get($detail->question_id) : null;

                if (! $detail || ! $questionModel) {
                    continue;
                }

                $vars = [];
                for ($i = 1; $i <= 10; $i++) {
                    $val = $detail->{"var{$i}"};
                    if ($val !== null && $val !== '') {
                        $vars[] = $val;
                    }
                }

                // Apply the stored shuffle order so each student sees options in a unique order.
                // var_order values are 1-based positions into the (0-based) $vars array, hence -1.
                // The SAME guard must gate both the vars shuffle and the correct-answer remap below:
                // if a detail was edited after assembly (var added/removed) the lengths diverge, and
                // remapping `correct` while leaving `vars` unshuffled would put them in different
                // coordinate spaces — highlighting the wrong option as "correct" in the review.
                $varOrder = $q['var_order'] ?? null;
                $applyOrder = $varOrder !== null && count($varOrder) === count($vars);

                if ($applyOrder) {
                    $shuffledVars = [];
                    foreach ($varOrder as $originalIdx) {
                        $shuffledVars[] = $vars[$originalIdx - 1] ?? null;
                    }
                    $vars = $shuffledVars;
                }

                // Remap correct original 1-based positions to 1-based display positions so they
                // align with user_answers (which are stored as 1-based display positions).
                $correct = null;
                if ($withCorrect) {
                    $originalCorrect = $detail->answers ?? [];

                    if (! $applyOrder) {
                        $correct = $originalCorrect;
                    } elseif ($questionModel->type === QuestionType::IS_MATCH) {
                        // IS_MATCH answers are positional (index 0 = pair 1, index 1 = pair 2);
                        // keep positions intact (map missing → null) instead of filtering, which
                        // would shift a pair's correct answer into the wrong slot — mirrors score().
                        $reverseOrder = array_flip($varOrder);
                        $correct = array_map(
                            fn ($origIdx) => isset($reverseOrder[$origIdx]) ? $reverseOrder[$origIdx] + 1 : null,
                            $originalCorrect
                        );
                    } else {
                        $reverseOrder = array_flip($varOrder);
                        $correct = array_values(array_filter(
                            array_map(fn ($origIdx) => isset($reverseOrder[$origIdx]) ? $reverseOrder[$origIdx] + 1 : null, $originalCorrect),
                            fn ($v) => $v !== null
                        ));
                    }
                }

                $questions[] = [
                    'index' => $idx,
                    'detail_id' => $detail->id,
                    'context' => $questionModel->type === QuestionType::IS_GROUP ? $questionModel->text : null,
                    'text' => $detail->question ?? $questionModel->text,
                    'type' => $questionModel->type,
                    'count_answers' => $questionModel->count_answers,
                    'vars' => $vars,
                    'user_answers' => $q['user_answers'],
                    'is_right' => $q['is_right'],
                    'points' => $q['points'] ?? null,
                    'max_points' => $q['max_points'] ?? null,
                    'correct' => $correct,
                ];
            }

            $result[] = [
                'test_subject_id' => $testSubject->id,
                'subject' => $testSubject->subject,
                'part' => $testSubject->part,
                'questions' => $questions,
                'score' => $testSubject->score,
                'max_score' => $testSubject->max_score,
                'answered' => collect($questions)->filter(function ($q) {
                    $answers = $q['user_answers'] ?? [];

                    return ! empty($answers) && collect($answers)->every(fn ($a) => $a !== null);
                })->count(),
            ];
        }

        return $result;
    }
}
