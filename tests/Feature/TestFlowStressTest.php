<?php

namespace Tests\Feature;

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
use App\Models\User;
use App\Services\TestAssemblyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Стресс-тест полного потока тестирования (build → save → finish → result/detail) «как студент»:
 * 500 прогонов, каждый по СЛУЧАЙНО выбранному варианту (part) с разным составом вопросов всех
 * типов (one / multi с разным count_answers / group / match). На каждом прогоне — новые
 * перестановки var_order и случайная стратегия ответа (верно / частично / неверно / пропуск).
 *
 * Ожидаемый балл считается независимо из «намерения» студента (в ИСХОДНЫХ позициях вариантов,
 * прочитанных из БД), затем переводится в display-позиции через var_order и отправляется через
 * настоящие HTTP-эндпоинты. Совпадение с баллом движка доказывает, что вся цепочка (shuffle,
 * позиционность match, лимит count_answers, частичные ответы) ничего не теряет и не путает.
 */
class TestFlowStressTest extends TestCase
{
    use RefreshDatabase;

    private const ITERATIONS = 500;

    private const VARIANTS = 10;

    private TestAssemblyService $service;

    private Subject $subject;

    private User $user;

    /** @var array<int, TestAccess> один access на каждый вариант (part) */
    private array $accesses = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TestAssemblyService::class);
        $this->user = User::factory()->create();
        $this->subject = Subject::factory()->create();

        // Несколько разных вариантов экзамена со случайным составом вопросов —
        // чтобы прогоны реально шли «по разным вопросам».
        for ($v = 1; $v <= self::VARIANTS; $v++) {
            $this->accesses[] = $this->makeVariant($v);
        }
    }

    public function test_student_flow_scores_correctly_across_500_runs(): void
    {
        for ($run = 1; $run <= self::ITERATIONS; $run++) {
            $access = $this->accesses[array_rand($this->accesses)];
            $test = $this->service->build($access->load('accessSubjects'), $this->user);

            [$payload, $expectedTotal] = $this->planAnswers($test);

            $this->actingAs($this->user)
                ->postJson(route('student.test.save', $test), $payload)
                ->assertOk();

            $this->actingAs($this->user)
                ->postJson(route('student.test.finish', $test))
                ->assertOk();

            // Страницы результата и разбора не должны падать («ошибка в конце»).
            $this->actingAs($this->user)->get(route('student.test.result', $test))->assertOk();
            $this->actingAs($this->user)->get(route('student.test.detail', $test))->assertOk();

            $test->refresh();

            $this->assertSame(
                $expectedTotal,
                $test->total_score,
                "Прогон #{$run} (access {$access->id}): ожидался балл {$expectedTotal}, движок насчитал {$test->total_score}."
            );

            foreach ($test->subjects as $testSubject) {
                foreach ($testSubject->questions as $q) {
                    $this->assertIsBool($q['is_right'], "Прогон #{$run}: is_right должен быть bool после завершения.");
                }
            }
        }
    }

    // ── Сборка вариантов ────────────────────────────────────────────────────────

    private function makeVariant(int $v): TestAccess
    {
        $part = Part::factory()->topic("Вариант {$v}")->create(['subject_id' => $this->subject->id]);

        Question::factory()->count(random_int(2, 4))->one($this->subject->id, $part->id)->create();
        Question::factory()->count(random_int(1, 3))->group($this->subject->id, $part->id)->create();
        Question::factory()->count(random_int(1, 3))->match($this->subject->id, $part->id)->create();

        // multi с разным числом правильных ответов (1..3) — проверяем лимит count_answers.
        foreach (range(1, random_int(2, 3)) as $i) {
            $this->makeMulti($part->id, random_int(1, 3));
        }

        $access = TestAccess::create([
            'type' => TestAccessType::Subject->value,
            'user_id' => $this->user->id,
            'is_active' => true,
            'attempts_limit' => 0,
        ]);

        TestAccessSubject::create([
            'test_access_id' => $access->id,
            'subject_id' => $this->subject->id,
            'part_type' => PartType::Topic->value,
            'part_id' => $part->id,
            'student_chooses_part' => false,
        ]);

        return $access;
    }

    /** Создаёт multi-вопрос с 6 вариантами, где первые $k — правильные. */
    private function makeMulti(int $partId, int $k): void
    {
        $q = Question::create([
            'subject_id' => $this->subject->id,
            'part_id' => $partId,
            'type' => QuestionType::SELECT_MULTI,
            'count_variants' => 6,
            'count_answers' => $k,
        ]);

        $vars = [];
        for ($i = 1; $i <= 6; $i++) {
            $vars["var{$i}"] = 'Вариант '.$i.' ('.($i <= $k ? 'правильный' : 'неправильный').')';
        }

        QuestionDetail::create([
            'question_id' => $q->id,
            'question' => "Multi-вопрос (k={$k}) №{$q->id}",
            'answers' => range(1, $k),
            ...$vars,
        ]);
    }

    // ── Планирование ответов ────────────────────────────────────────────────────

    /**
     * Строит payload для save (как фронтенд) и независимо считает ожидаемый суммарный балл.
     *
     * @return array{0: array<string, mixed>, 1: int}
     */
    private function planAnswers(Test $test): array
    {
        $subjectsPayload = [];
        $expectedTotal = 0;

        foreach ($test->subjects as $testSubject) {
            $questionsPayload = [];

            foreach ($testSubject->questions as $q) {
                $detail = QuestionDetail::find($q['detail_id']);
                $question = Question::find($detail->question_id);
                $varOrder = $q['var_order'];
                $correct = $detail->answers;
                $varCount = $this->varCountOf($detail);

                [$displays, $points] = $this->chooseFor($question->type, $varOrder, $correct, $varCount, $question->count_answers);
                $expectedTotal += $points;

                // Пропуск вопроса: фронтенд просто ничего не присылает для него.
                if ($displays === null) {
                    continue;
                }

                $questionsPayload[] = [
                    'detail_id' => $q['detail_id'],
                    'user_answers' => $displays,
                ];
            }

            $subjectsPayload[] = [
                'test_subject_id' => $testSubject->id,
                'questions' => $questionsPayload,
            ];
        }

        return [['subjects' => $subjectsPayload], $expectedTotal];
    }

    /**
     * Выбирает ответ для одного вопроса по случайной стратегии и возвращает
     * [display-позиции для payload (или null = пропуск), ожидаемые баллы].
     *
     * @param  int[]  $varOrder
     * @param  int[]  $correct
     * @return array{0: array<int, int|null>|null, 1: int}
     */
    private function chooseFor(QuestionType $type, array $varOrder, array $correct, int $varCount, int $countAnswers): array
    {
        $displayFor = fn (int $orig): int => array_search($orig, $varOrder) + 1;

        return match ($type) {
            QuestionType::SELECT_ONE, QuestionType::IS_GROUP => $this->chooseOne($displayFor, $correct, $varCount),
            QuestionType::SELECT_MULTI => $this->chooseMulti($displayFor, $correct, $varCount, $countAnswers),
            QuestionType::IS_MATCH => $this->chooseMatch($displayFor, $correct, $varCount),
        };
    }

    /**
     * one/group: один верный — $correct[0].
     *
     * @param  int[]  $correct
     * @return array{0: array<int, int>|null, 1: int}
     */
    private function chooseOne(callable $displayFor, array $correct, int $varCount): array
    {
        return match (random_int(0, 2)) {
            0 => [[$displayFor($correct[0])], 1],                         // верно
            1 => [[$displayFor($this->wrong($correct, 1, $varCount))], 0], // неверно
            default => [null, 0],                                          // пропуск
        };
    }

    /**
     * multi: правильные — $correct (size = $countAnswers). Балл по правилу движка:
     * все верные → 2; ровно одного не хватает (matching = k-1, без лишних сверх k) → 1; иначе 0.
     *
     * @param  int[]  $correct
     * @return array{0: array<int, int>|null, 1: int}
     */
    private function chooseMulti(callable $displayFor, array $correct, int $varCount, int $countAnswers): array
    {
        $toDisplays = fn (array $origins) => array_map($displayFor, $origins);

        $intended = match (random_int(0, 5)) {
            0 => $correct,                                                    // все верные
            1 => array_slice($correct, 0, max(1, $countAnswers - 1)),         // часть верных
            2 => [$correct[0], $this->wrong($correct, 1, $varCount)],         // один верный + один лишний
            3 => [$this->wrong($correct, 1, $varCount)],                      // один неверный
            4 => $this->wrongSet($correct, $varCount, $countAnswers),         // все неверные
            default => [],                                                    // пропуск
        };

        if ($intended === []) {
            return [null, 0];
        }

        return [$toDisplays($intended), $this->expectedMulti($intended, $correct, $countAnswers)];
    }

    /**
     * match: позиционно, пара1→$correct[0], пара2→$correct[1]. Варианты — позиции 3..$varCount.
     *
     * @param  int[]  $correct
     * @return array{0: array<int, int|null>|null, 1: int}
     */
    private function chooseMatch(callable $displayFor, array $correct, int $varCount): array
    {
        [$c1, $c2] = $correct;

        return match (random_int(0, 5)) {
            0 => [[$displayFor($c1), $displayFor($c2)], 2],                          // обе верны
            1 => [[$displayFor($c2), $displayFor($c1)], 0],                          // перепутаны местами
            2 => [[null, $displayFor($c2)], 1],                                      // только пара 2
            3 => [[$displayFor($c1), null], 1],                                      // только пара 1
            4 => [[$displayFor($c1), $displayFor($this->wrong($correct, 3, $varCount))], 1], // пара1 верно, пара2 лишний
            default => [null, 0],                                                    // пропуск
        };
    }

    // ── Вспомогательные расчёты ──────────────────────────────────────────────────

    /**
     * Зеркало TestAssemblyService::scoreSelectMulti для независимой сверки ожидаемого балла.
     *
     * @param  int[]  $intended
     * @param  int[]  $correct
     */
    private function expectedMulti(array $intended, array $correct, int $countAnswers): int
    {
        $intended = array_slice($intended, 0, $countAnswers);
        $matching = count(array_intersect($intended, $correct));

        if ($matching === $countAnswers) {
            return 2;
        }

        if ($matching > 0 && ($countAnswers - $matching) === 1) {
            return 1;
        }

        return 0;
    }

    /** Кол-во непустых вариантов var1..var10 у detail. */
    private function varCountOf(QuestionDetail $detail): int
    {
        $count = 0;
        for ($i = 1; $i <= 10; $i++) {
            if ($detail->{"var{$i}"} !== null && $detail->{"var{$i}"} !== '') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Случайная «неверная» исходная позиция в [$min..$varCount], не входящая в $exclude.
     *
     * @param  int[]  $exclude
     */
    private function wrong(array $exclude, int $min, int $varCount): int
    {
        $pool = array_values(array_diff(range($min, $varCount), $exclude));

        return $pool[array_rand($pool)];
    }

    /**
     * Набор из $count неверных исходных позиций.
     *
     * @param  int[]  $exclude
     * @return int[]
     */
    private function wrongSet(array $exclude, int $varCount, int $count): array
    {
        $pool = array_values(array_diff(range(1, $varCount), $exclude));
        shuffle($pool);

        return array_slice($pool, 0, $count);
    }
}
