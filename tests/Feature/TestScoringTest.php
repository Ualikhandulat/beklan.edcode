<?php

namespace Tests\Feature;

use App\Enums\PartType;
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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TestScoringTest extends TestCase
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

    public function test_correct_answers_in_shuffled_order_score_full_marks(): void
    {
        Question::factory()->one($this->subject->id, $this->part->id)->create();
        Question::factory()->multi($this->subject->id, $this->part->id)->create();
        Question::factory()->match($this->subject->id, $this->part->id)->create();

        $test = $this->buildTest();

        $this->answer($test, correctly: true);
        $this->service->score($test->fresh());

        $test->refresh();
        $this->assertGreaterThan(0, $test->max_score);
        $this->assertSame($test->max_score, $test->total_score, 'Все правильные ответы должны дать максимум баллов.');
    }

    public function test_wrong_answers_score_zero(): void
    {
        Question::factory()->one($this->subject->id, $this->part->id)->create();
        Question::factory()->multi($this->subject->id, $this->part->id)->create();

        $test = $this->buildTest();

        $this->answer($test, correctly: false);
        $this->service->score($test->fresh());

        $this->assertSame(0, $test->fresh()->total_score, 'Неверные ответы должны дать 0 баллов.');
    }

    /**
     * Проверяет систему баллов ҰБТ для вопросов 36–40 (1–3 правильных, максимум 2 балла)
     * по каждой строке официальных таблиц.
     *
     * @param  int[]  $correct  правильные позиции (1-based)
     * @param  int[]  $selected  выбранные студентом позиции (1-based)
     */
    #[DataProvider('multiScoringCases')]
    public function test_multi_scoring_follows_unt_rules(array $correct, array $selected, int $expected, string $label): void
    {
        $method = new \ReflectionMethod(TestAssemblyService::class, 'scoreSelectMulti');

        $this->assertSame($expected, $method->invoke($this->service, $selected, $correct), $label);
    }

    /**
     * @return array<string, array{0: int[], 1: int[], 2: int, 3: string}>
     */
    public static function multiScoringCases(): array
    {
        return [
            // 1 правильный ответ
            '1 верный: только верный = 2' => [[1], [1], 2, '1 дұрыс жауап (тек қана) = 2 балл'],
            '1 верный: верный + 1 неверный = 1' => [[1], [1, 3], 1, '1 дұрыс + 1 қате = 1 балл'],
            '1 верный: 2 неверных = 0' => [[1], [3, 4], 0, '2 немесе одан көп қате = 0 балл'],
            '1 верный: верный + 2 неверных = 0' => [[1], [1, 3, 4], 0, '2 қате болса = 0 балл'],
            '1 верный: ничего = 0' => [[1], [], 0, 'дұрыс жауап жоқ = 0 балл'],

            // 2 правильных ответа
            '2 верных: оба верных = 2' => [[1, 2], [1, 2], 2, 'екі дұрыс жауапты дәл = 2 балл'],
            '2 верных: только 1 верный = 1' => [[1, 2], [1], 1, 'тек 1 дұрыс = 1 балл'],
            '2 верных: 1 верный + 1 неверный = 1' => [[1, 2], [1, 3], 1, '1 дұрыс + 1 қате = 1 балл'],
            '2 верных: 2 верных + 1 неверный = 1' => [[1, 2], [1, 2, 3], 1, '2 дұрыс + 1 қате = 1 балл'],
            '2 верных: 2 неверных = 0' => [[1, 2], [3, 4], 0, '2 немесе одан көп қате = 0 балл'],
            '2 верных: 1 верный + 2 неверных = 0' => [[1, 2], [1, 3, 4], 0, '2 қате болса = 0 балл'],

            // 3 правильных ответа
            '3 верных: все три верных = 2' => [[1, 2, 3], [1, 2, 3], 2, 'үш дұрыс жауапты дәл = 2 балл'],
            '3 верных: 2 верных = 1' => [[1, 2, 3], [1, 2], 1, '2 дұрыс = 1 балл'],
            '3 верных: 2 верных + 1 неверный = 1' => [[1, 2, 3], [1, 2, 4], 1, '2 дұрыс + 1 қате = 1 балл'],
            '3 верных: 3 верных + 1 неверный = 1' => [[1, 2, 3], [1, 2, 3, 4], 1, '3 дұрыс + 1 қате = 1 балл'],
            '3 верных: только 1 верный = 0' => [[1, 2, 3], [1], 0, 'тек 1 дұрыс = 0 балл'],
            '3 верных: 1 верный + 1 неверный = 0' => [[1, 2, 3], [1, 4], 0, 'тек 1 дұрыс таңдаған = 0 балл'],
            '3 верных: 2 верных + 2 неверных = 0' => [[1, 2, 3], [1, 2, 4, 5], 0, '2 немесе одан көп қате = 0 балл'],
        ];
    }

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

    /**
     * Заполняет ответы студента так, как это делает фронтенд: выбирает 1-based позицию
     * отображения, на которой стоит нужный вариант (с учётом перемешивания var_order).
     */
    private function answer(Test $test, bool $correctly): void
    {
        foreach ($test->subjects as $testSubject) {
            $questions = $testSubject->questions;

            foreach ($questions as &$q) {
                $detail = QuestionDetail::find($q['detail_id']);
                $varOrder = $q['var_order'];
                $correctOriginals = $detail->answers; // 1-based позиции в исходном массиве

                // display position (1-based) -> original: $varOrder[display - 1]
                $displayFor = fn (int $orig) => array_search($orig, $varOrder) + 1;

                if ($correctly) {
                    $q['user_answers'] = array_map($displayFor, $correctOriginals);
                } else {
                    $allDisplays = range(1, count($varOrder));
                    $correctDisplays = array_map($displayFor, $correctOriginals);
                    $wrong = array_values(array_diff($allDisplays, $correctDisplays));
                    $q['user_answers'] = [$wrong[0]];
                }
            }
            unset($q);

            $testSubject->update(['questions' => $questions]);
        }
    }
}
