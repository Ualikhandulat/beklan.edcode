<?php

namespace App\Services;

use App\Enums\PartType;
use App\Enums\QuestionType;
use App\Enums\TestAccessType;
use App\Models\Part;
use App\Models\Question;
use App\Models\QuestionDetail;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\TestAccessSubject;
use App\Models\TestSubject;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestAssemblyService
{
    /**
     * Create a Test record and assemble questions for a given access.
     *
     * @param  array<string, mixed>  $choices  Student's runtime choices (subject IDs, nusqa number)
     */
    public function build(TestAccess $access, User $user, array $choices = []): Test
    {
        return DB::transaction(function () use ($access, $user, $choices) {
            $attemptNumber = Test::where('test_access_id', $access->id)
                ->where('user_id', $user->id)
                ->count() + 1;

            $test = Test::create([
                'test_access_id' => $access->id,
                'user_id' => $user->id,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
            ]);

            if ($access->type === TestAccessType::Ent) {
                $this->assembleEnt($test, $access, $choices);
            } else {
                $this->assembleSubject($test, $access, $choices);
            }

            // Set max_score from assembled subjects
            $test->update(['max_score' => $test->subjects()->sum('max_score')]);

            return $test;
        });
    }

    // ── ENT ─────────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $choices */
    private function assembleEnt(Test $test, TestAccess $access, array $choices): void
    {
        $nusqaNumber = $this->resolveNusqaNumber($access, $choices);

        $subjectIds = $access->accessSubjects->pluck('subject_id')->all();

        // If student chooses elective subjects, take from choices
        if ($access->student_chooses_subject) {
            $mandatoryIds = Subject::where('is_mandatory', true)->pluck('id')->all();
            $electiveIds = array_filter((array) ($choices['elective_subject_ids'] ?? []));
            $subjectIds = array_merge($mandatoryIds, array_slice($electiveIds, 0, 2));
        }

        foreach ($subjectIds as $subjectId) {
            $part = $this->resolveNusqaPart($subjectId, $nusqaNumber);
            $detailIds = $this->pickDetails($subjectId, $part?->id);
            $maxScore = count($detailIds);

            TestSubject::create([
                'test_id' => $test->id,
                'subject_id' => $subjectId,
                'part_id' => $part?->id,
                'questions' => $this->initQuestions($detailIds),
                'max_score' => $maxScore,
            ]);
        }
    }

    // ── Subject ──────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $choices */
    private function assembleSubject(Test $test, TestAccess $access, array $choices): void
    {
        $cfg = $access->accessSubjects->first();

        if (! $cfg) {
            return;
        }

        $partId = $this->resolveSubjectPartId($cfg, $choices);
        $limit = $access->question_count > 0 ? $access->question_count : null;

        $detailIds = $this->pickDetails($cfg->subject_id, $partId, $limit);
        $maxScore = count($detailIds);

        TestSubject::create([
            'test_id' => $test->id,
            'subject_id' => $cfg->subject_id,
            'part_id' => $partId,
            'questions' => $this->initQuestions($detailIds),
            'max_score' => $maxScore,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resolve which нұсқа number to use for an ENT test.
     *
     * @param  array<string, mixed>  $choices
     */
    private function resolveNusqaNumber(TestAccess $access, array $choices): ?int
    {
        if ($access->student_chooses_nusqa) {
            return isset($choices['nusqa_number']) ? (int) $choices['nusqa_number'] : null;
        }

        return $access->nusqa_number;
    }

    /**
     * Find the Part of type Нұсқа for a subject.
     * If nusqaNumber is given, picks the Nth Нұсқа part (1-indexed, ordered by id).
     * Otherwise picks a random one.
     */
    private function resolveNusqaPart(int $subjectId, ?int $nusqaNumber): ?Part
    {
        $query = Part::where('subject_id', $subjectId)
            ->where('type', PartType::Nusqa->value)
            ->orderBy('id');

        if ($nusqaNumber !== null) {
            return $query->skip($nusqaNumber - 1)->first();
        }

        return $query->inRandomOrder()->first();
    }

    /**
     * Resolve part_id for a Subject-type access subject config.
     *
     * @param  array<string, mixed>  $choices
     */
    private function resolveSubjectPartId(TestAccessSubject $cfg, array $choices): ?int
    {
        if (! $cfg->part_type) {
            return null; // All questions in subject, no part filter
        }

        if ($cfg->student_chooses_part) {
            return isset($choices['part_id']) ? (int) $choices['part_id'] : null;
        }

        if ($cfg->part_id) {
            return $cfg->part_id;
        }

        // Random part of the specified type
        $part = Part::where('subject_id', $cfg->subject_id)
            ->where('type', $cfg->part_type->value)
            ->inRandomOrder()
            ->first();

        return $part?->id;
    }

    /**
     * Fetch QuestionDetail IDs for the given subject (and optionally part), ordered by type.
     *
     * Order: SELECT_ONE → IS_GROUP → SELECT_MULTI → IS_MATCH (same as smstudy)
     *
     * @return int[]
     */
    private function pickDetails(int $subjectId, ?int $partId, ?int $limit = null): array
    {
        $order = [
            QuestionType::SELECT_ONE->value,
            QuestionType::IS_GROUP->value,
            QuestionType::SELECT_MULTI->value,
            QuestionType::IS_MATCH->value,
        ];

        $query = Question::join('question_details as qd', 'qd.question_id', '=', 'questions.id')
            ->where('questions.subject_id', $subjectId)
            ->whereNull('questions.deleted_at')
            ->whereNull('qd.deleted_at');

        if ($partId !== null) {
            $query->where('questions.part_id', $partId);
        }

        foreach ($order as $type) {
            $query->orderByRaw('CASE WHEN questions.type = ? THEN 0 ELSE 1 END', [$type]);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->pluck('qd.id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Initialise the questions JSON with a randomised var_order per question.
     * This ensures answer options are shuffled differently on every attempt.
     *
     * @param  int[]  $detailIds
     * @return array<int, array{detail_id: int, user_answers: array, is_right: null, var_order: int[]}>
     */
    private function initQuestions(array $detailIds): array
    {
        if (empty($detailIds)) {
            return [];
        }

        $details = QuestionDetail::whereIn('id', $detailIds)->get()->keyBy('id');
        $questionModels = Question::whereIn('id', $details->pluck('question_id'))->get()->keyBy('id');

        return array_map(function ($id) use ($details, $questionModels) {
            $detail = $details->get($id);
            $qModel = $detail ? $questionModels->get($detail->question_id) : null;

            $varCount = 0;
            if ($detail) {
                for ($i = 1; $i <= 10; $i++) {
                    if ($detail->{"var{$i}"} !== null && $detail->{"var{$i}"} !== '') {
                        $varCount++;
                    }
                }
            }

            return [
                'detail_id' => $id,
                'user_answers' => [],
                'is_right' => null,
                'var_order' => $this->generateVarOrder($qModel?->type, $varCount),
            ];
        }, $detailIds);
    }

    /**
     * Generate a shuffled order for answer option vars.
     * IS_MATCH keeps its original order; all other types are fully shuffled.
     *
     * @return int[]
     */
    private function generateVarOrder(?QuestionType $type, int $varCount): array
    {
        $order = range(0, max(0, $varCount - 1));

        if ($varCount < 2 || $type === null || $type === QuestionType::IS_MATCH) {
            return $order;
        }

        shuffle($order);

        return $order;
    }

    // ── Scoring ───────────────────────────────────────────────────────────────

    /**
     * Calculate scores for all subjects and mark the test as completed.
     */
    public function score(Test $test): void
    {
        DB::transaction(function () use ($test) {
            $totalScore = 0;

            foreach ($test->subjects as $testSubject) {
                $subjectScore = 0;
                $questions = $testSubject->questions;

                foreach ($questions as &$q) {
                    $detail = QuestionDetail::find($q['detail_id']);
                    // `question` is both a column and a relation on QuestionDetail — use question_id to avoid the clash
                    $questionModel = $detail ? Question::find($detail->question_id) : null;

                    if (! $detail || ! $questionModel) {
                        $q['is_right'] = false;

                        continue;
                    }

                    $correct = $detail->answers;
                    $userAnswers = (array) ($q['user_answers'] ?? []);
                    $type = $questionModel->type;
                    $varOrder = $q['var_order'] ?? null;

                    // Remap display-order indices back to original var indices before scoring
                    if ($varOrder !== null && $type !== QuestionType::IS_MATCH && ! empty($userAnswers)) {
                        $userAnswers = array_values(array_filter(
                            array_map(fn ($displayIdx) => $varOrder[$displayIdx] ?? null, $userAnswers),
                            fn ($v) => $v !== null
                        ));
                    }

                    $pts = $this->calculatePoints($type, $userAnswers, $correct, $questionModel);
                    $subjectScore += $pts;
                    $q['is_right'] = $pts > 0;
                }

                unset($q);

                $testSubject->update(['questions' => $questions, 'score' => $subjectScore]);
                $totalScore += $subjectScore;
            }

            $test->update([
                'total_score' => $totalScore,
                'completed_at' => now(),
            ]);

            // Cache key format mirrored in DashboardController::buildStats() — drop it so the
            // dashboard reloads fresh progress/activity data after this completed attempt.
            Cache::forget("student.dashboard.stats.{$test->user_id}");
        });
    }

    /**
     * Calculate points for a single question based on its type.
     *
     * SELECT_ONE / IS_GROUP : 1 or 0
     * SELECT_MULTI           : 2 / 1 / 0  (based on matching count)
     * IS_MATCH               : 0–2  (positional pair check)
     *
     * @param  int[]  $userAnswers
     * @param  int[]  $correctAnswers
     */
    private function calculatePoints(
        QuestionType $type,
        array $userAnswers,
        array $correctAnswers,
        Question $question
    ): int {
        return match ($type) {
            QuestionType::SELECT_ONE, QuestionType::IS_GROUP => $this->scoreSelectOne($userAnswers, $correctAnswers),
            QuestionType::SELECT_MULTI => $this->scoreSelectMulti($userAnswers, $correctAnswers, $question->count_answers),
            QuestionType::IS_MATCH => $this->scoreMatch($userAnswers, $correctAnswers),
        };
    }

    /** @param int[] $userAnswers @param int[] $correctAnswers */
    private function scoreSelectOne(array $userAnswers, array $correctAnswers): int
    {
        return isset($userAnswers[0]) && isset($correctAnswers[0]) && $userAnswers[0] === $correctAnswers[0] ? 1 : 0;
    }

    /** @param int[] $userAnswers @param int[] $correctAnswers */
    private function scoreSelectMulti(array $userAnswers, array $correctAnswers, int $countAnswers): int
    {
        $userAnswers = array_slice($userAnswers, 0, $countAnswers);
        $matching = count(array_intersect($userAnswers, $correctAnswers));

        if ($matching === $countAnswers) {
            return 2;
        }

        if ($matching > 0 && ($countAnswers - $matching) === 1) {
            return 1;
        }

        return 0;
    }

    /** @param int[] $userAnswers @param int[] $correctAnswers */
    private function scoreMatch(array $userAnswers, array $correctAnswers): int
    {
        $score = 0;

        if (isset($userAnswers[0], $correctAnswers[0]) && $userAnswers[0] === $correctAnswers[0]) {
            $score++;
        }

        if (isset($userAnswers[1], $correctAnswers[1]) && $userAnswers[1] === $correctAnswers[1]) {
            $score++;
        }

        return $score;
    }
}
