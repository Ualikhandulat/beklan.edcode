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
