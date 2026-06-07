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
     * Канонические квоты типов вопросов для обязательных предметов ЕНТ РК, по названию
     * предмета (как в EntDataSeeder) и значению QuestionType:
     * История Казахстана — 20 SELECT_ONE (20 баллов); Грамотность чтения — 10 IS_GROUP
     * вопросов к контекстам (10 баллов); Математическая грамотность — 10
     * SELECT_ONE (10 баллов).
     *
     * @var array<string, array<string, int>>
     */
    private const MANDATORY_QUOTAS = [
        'История Казахстана' => [
            'one' => 20,
        ],
        'Грамотность чтения' => [
            'group' => 10,
        ],
        'Математическая грамотность' => [
            'one' => 10,
        ],
    ];

    /**
     * Каноническая квота типов вопросов для профильных (элективных) предметов ЕНТ РК:
     * 25 SELECT_ONE + 5 IS_GROUP + 5 IS_MATCH + 5 SELECT_MULTI = 40 заданий / 50 баллов.
     *
     * @var array<string, int>
     */
    private const PROFILE_QUOTA = [
        'one' => 25,
        'group' => 5,
        'match' => 5,
        'multi' => 5,
    ];

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

        $subjects = Subject::whereIn('id', $subjectIds)->get()->keyBy('id');

        foreach ($subjectIds as $subjectId) {
            $subject = $subjects->get($subjectId);
            $part = $this->resolveNusqaPart($subjectId, $nusqaNumber);
            $quota = self::MANDATORY_QUOTAS[$subject?->title] ?? self::PROFILE_QUOTA;
            $detailIds = $this->pickDetailsByQuota($subjectId, $part?->id, $quota);
            ['questions' => $questions, 'max_score' => $maxScore] = $this->initQuestions($detailIds);

            TestSubject::create([
                'test_id' => $test->id,
                'subject_id' => $subjectId,
                'part_id' => $part?->id,
                'questions' => $questions,
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
        ['questions' => $questions, 'max_score' => $maxScore] = $this->initQuestions($detailIds);

        TestSubject::create([
            'test_id' => $test->id,
            'subject_id' => $cfg->subject_id,
            'part_id' => $partId,
            'questions' => $questions,
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
     * Подбирает ID QuestionDetail по каноническим квотам типов вопросов ЕНТ РК для предмета/части.
     *
     * Берёт до нужного количества каждого типа (в порядке квоты), сортируя детерминированно
     * по id — нұсқа представляет собой фиксированный вариант экзамена, а не случайную выборку
     * на каждую попытку. Если у предмета/части не хватает контента нужного типа, берём всё,
     * что есть, и идём дальше — каноническая структура является целью, но нехватка контента
     * не должна ломать сборку теста (админы должны заметить пробелы и добавить вопросы).
     *
     * @param  array<string, int>  $quota  значение QuestionType => нужное количество вопросов
     * @return int[]
     */
    private function pickDetailsByQuota(int $subjectId, ?int $partId, array $quota): array
    {
        $detailIds = [];

        foreach ($quota as $typeValue => $count) {
            if ($count <= 0) {
                continue;
            }

            if ($typeValue === QuestionType::IS_GROUP->value) {
                $detailIds = array_merge($detailIds, $this->pickGroupContextDetails($subjectId, $partId, $count));

                continue;
            }

            $query = Question::join('question_details as qd', 'qd.question_id', '=', 'questions.id')
                ->where('questions.subject_id', $subjectId)
                ->where('questions.type', $typeValue)
                ->whereNull('questions.deleted_at')
                ->whereNull('qd.deleted_at');

            if ($partId !== null) {
                $query->where('questions.part_id', $partId);
            }

            $detailIds = array_merge(
                $detailIds,
                $query->orderBy('qd.id')->limit($count)->pluck('qd.id')->map(fn ($id) => (int) $id)->all()
            );
        }

        return $detailIds;
    }

    /**
     * Подбирает QuestionDetail для контекстных (IS_GROUP) вопросов целыми контекстами —
     * один контекст (текст для чтения) всегда отдаёт студенту все свои вопросы вместе,
     * никогда не разрывая их по границе квоты. Берёт контексты по порядку id, пока
     * не наберётся нужное количество вопросов (итог может немного превышать $count,
     * если последний добавленный контекст содержит несколько вопросов).
     *
     * @return int[]
     */
    private function pickGroupContextDetails(int $subjectId, ?int $partId, int $count): array
    {
        $query = Question::where('subject_id', $subjectId)
            ->where('type', QuestionType::IS_GROUP->value)
            ->whereNull('deleted_at')
            ->with(['details' => fn ($q) => $q->whereNull('deleted_at')->orderBy('id')]);

        if ($partId !== null) {
            $query->where('part_id', $partId);
        }

        $detailIds = [];

        foreach ($query->orderBy('id')->get() as $context) {
            if (count($detailIds) >= $count) {
                break;
            }

            $detailIds = array_merge(
                $detailIds,
                $context->details->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
        }

        return $detailIds;
    }

    /**
     * Формирует JSON вопросов со случайным var_order для каждого вопроса и считает
     * max_score предмета как сумму максимальных баллов по каждому вопросу
     * (1 для SELECT_ONE/IS_GROUP, 2 для SELECT_MULTI/IS_MATCH — см. calculatePoints).
     * Это гарантирует, что варианты ответов перемешиваются по-разному в каждой попытке,
     * а max_score отражает каноническую систему баллов ЕНТ (20/10/10/50/50 = 140 баллов
     * при полностью заполненной квоте предмета).
     *
     * @param  int[]  $detailIds
     * @return array{questions: array<int, array{detail_id: int, user_answers: array, is_right: null, var_order: int[]}>, max_score: int}
     */
    private function initQuestions(array $detailIds): array
    {
        if (empty($detailIds)) {
            return ['questions' => [], 'max_score' => 0];
        }

        $details = QuestionDetail::whereIn('id', $detailIds)->get()->keyBy('id');
        $questionModels = Question::whereIn('id', $details->pluck('question_id'))->get()->keyBy('id');

        $maxScore = 0;
        $questions = array_map(function ($id) use ($details, $questionModels, &$maxScore) {
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

            if ($qModel) {
                // SELECT_ONE/IS_GROUP — 0 или 1 балл, SELECT_MULTI/IS_MATCH — 0-2 балла (см. calculatePoints)
                $maxScore += match ($qModel->type) {
                    QuestionType::SELECT_ONE, QuestionType::IS_GROUP => 1,
                    QuestionType::SELECT_MULTI, QuestionType::IS_MATCH => 2,
                };
            }

            return [
                'detail_id' => $id,
                'user_answers' => [],
                'is_right' => null,
                'var_order' => $this->generateVarOrder($qModel?->type, $varCount),
            ];
        }, $detailIds);

        return ['questions' => $questions, 'max_score' => $maxScore];
    }

    /**
     * Generate a shuffled order for answer option vars.
     *
     * IS_MATCH keeps its first two vars (the left-column prompts) fixed and only
     * shuffles the remaining answer-option vars (var5-var8 → display positions 2+).
     * All other types are fully shuffled.
     *
     * @return int[]
     */
    private function generateVarOrder(?QuestionType $type, int $varCount): array
    {
        $order = range(0, max(0, $varCount - 1));

        if ($varCount < 2 || $type === null) {
            return $order;
        }

        if ($type === QuestionType::IS_MATCH) {
            if ($varCount <= 2) {
                return $order;
            }

            $options = array_slice($order, 2);
            shuffle($options);

            return array_merge([0, 1], $options);
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

                    // Remap display-order indices back to original var indices before scoring.
                    // IS_MATCH answers are positional (index 0 = pair 1, index 1 = pair 2), so
                    // nulls must be preserved rather than filtered out.
                    if ($varOrder !== null && ! empty($userAnswers)) {
                        if ($type === QuestionType::IS_MATCH) {
                            $userAnswers = array_map(
                                fn ($displayIdx) => $displayIdx === null ? null : ($varOrder[$displayIdx] ?? null),
                                $userAnswers
                            );
                        } else {
                            $userAnswers = array_values(array_filter(
                                array_map(fn ($displayIdx) => $varOrder[$displayIdx] ?? null, $userAnswers),
                                fn ($v) => $v !== null
                            ));
                        }
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
