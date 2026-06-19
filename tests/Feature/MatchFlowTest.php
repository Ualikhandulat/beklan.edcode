<?php

namespace Tests\Feature;

use App\Enums\PartType;
use App\Enums\TestAccessType;
use App\Models\Part;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\TestAccessSubject;
use App\Models\User;
use App\Services\TestAssemblyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проходит тест на «соответствие» через настоящие HTTP-эндпоинты (save → finish),
 * имитируя ровно то, что отправляет фронтенд (включая фильтрацию null в bulkSave).
 */
class MatchFlowTest extends TestCase
{
    use RefreshDatabase;

    private TestAssemblyService $service;

    private Subject $subject;

    private Part $part;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TestAssemblyService::class);
        $this->user = User::factory()->create();
        $this->subject = Subject::factory()->create();
        $this->part = Part::factory()->topic('Тема 1')->create(['subject_id' => $this->subject->id]);
    }

    public function test_match_both_pairs_answered_via_http_scores_full(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        [$q, $varOrder] = $this->firstQuestion($test);
        $displayFor = fn (int $orig) => array_search($orig, $varOrder) + 1;

        // Обе пары отвечены правильно: pair1 -> answers[0]=3, pair2 -> answers[1]=4
        $userAnswers = [$displayFor(3), $displayFor(4)];

        $this->saveAndFinish($test, $q['detail_id'], $userAnswers);

        $test->refresh();
        $this->assertSame(2, $test->total_score, 'Обе пары верны — должно быть 2 балла.');
    }

    public function test_match_only_second_pair_answered_via_http_scores_one(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        [$q, $varOrder] = $this->firstQuestion($test);
        $displayFor = fn (int $orig) => array_search($orig, $varOrder) + 1;

        // Студент ответил ТОЛЬКО на вторую пару (правильно), первую оставил пустой.
        // Фронтенд хранит [null, dp2]; bulkSave отправляет [null, dp2].
        $userAnswers = [null, $displayFor(4)];

        $this->saveAndFinish($test, $q['detail_id'], $userAnswers);

        $test->refresh();
        $this->assertSame(1, $test->total_score, 'Верна только вторая пара — должен быть 1 балл за неё.');
    }

    public function test_match_only_first_pair_answered_via_http_scores_one(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        [$q, $varOrder] = $this->firstQuestion($test);
        $displayFor = fn (int $orig) => array_search($orig, $varOrder) + 1;

        // Только первая пара (правильно), вторая пустая.
        $this->saveAndFinish($test, $q['detail_id'], [$displayFor(3), null]);

        $this->assertSame(1, $test->refresh()->total_score, 'Верна только первая пара — 1 балл.');
    }

    public function test_match_swapped_answers_score_zero(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        [$q, $varOrder] = $this->firstQuestion($test);
        $displayFor = fn (int $orig) => array_search($orig, $varOrder) + 1;

        // Ответы перепутаны местами: pair1 получил эталон пары 2 и наоборот.
        $this->saveAndFinish($test, $q['detail_id'], [$displayFor(4), $displayFor(3)]);

        $this->assertSame(0, $test->refresh()->total_score, 'Перепутанные ответы — 0 баллов.');
    }

    public function test_match_one_right_one_wrong_scores_one(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        [$q, $varOrder] = $this->firstQuestion($test);
        $displayFor = fn (int $orig) => array_search($orig, $varOrder) + 1;

        // pair1 верно (эталон 3), pair2 неверно (дистрактор, эталон 5 вместо 4).
        $this->saveAndFinish($test, $q['detail_id'], [$displayFor(3), $displayFor(5)]);

        $this->assertSame(1, $test->refresh()->total_score, 'Одна пара верна — 1 балл.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildTest(): Test
    {
        $access = TestAccess::create([
            'type' => TestAccessType::Subject->value,
            'user_id' => $this->user->id,
            'is_active' => true,
            'attempts_limit' => 1,
        ]);

        TestAccessSubject::create([
            'test_access_id' => $access->id,
            'subject_id' => $this->subject->id,
            'part_type' => PartType::Topic->value,
            'part_id' => $this->part->id,
            'student_chooses_part' => false,
        ]);

        return $this->service->build($access->load('accessSubjects'), $this->user);
    }

    /** @return array{0: array<string, mixed>, 1: int[]} */
    private function firstQuestion(Test $test): array
    {
        $testSubject = $test->subjects->first();
        $q = $testSubject->questions[0];

        return [$q, $q['var_order']];
    }

    /**
     * Имитирует то, что делает фронтенд: bulkSave отфильтровывает null,
     * затем POST на save, затем POST на finish.
     *
     * @param  array<int, int|null>  $userAnswers
     */
    private function saveAndFinish(Test $test, int $detailId, array $userAnswers): void
    {
        $testSubject = $test->subjects->first();

        // Фронтенд (bulkSave) отправляет позиционный массив как есть, сохраняя null
        // для неотвеченных пар — позиции важны для IS_MATCH.
        $this->actingAs($this->user)
            ->postJson(route('student.test.save', $test), [
                'subjects' => [[
                    'test_subject_id' => $testSubject->id,
                    'questions' => [[
                        'detail_id' => $detailId,
                        'user_answers' => $userAnswers,
                    ]],
                ]],
            ])->assertOk();

        $this->actingAs($this->user)
            ->postJson(route('student.test.finish', $test))
            ->assertOk();
    }
}
