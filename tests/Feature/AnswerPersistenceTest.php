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
 * Ответы, сохранённые через save, должны вернуться на странице process после перезагрузки,
 * а снятый ответ (пустой массив) — должен затереть ранее сохранённый.
 */
class AnswerPersistenceTest extends TestCase
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

    public function test_saved_answer_is_returned_on_process_page_reload(): void
    {
        Question::factory()->one($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();
        $testSubject = $test->subjects->first();
        $detailId = $testSubject->questions[0]['detail_id'];

        $this->save($test, $testSubject->id, $detailId, [2]);

        $response = $this->actingAs($this->user)->get(route('student.test.process', $test))->assertOk();
        $subjectsData = $response->viewData('subjectsData');

        $this->assertSame([2], $subjectsData[0]['questions'][0]['user_answers']);
        $this->assertSame(1, $subjectsData[0]['answered']);
    }

    public function test_deselected_answer_is_cleared_on_save(): void
    {
        Question::factory()->one($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();
        $testSubject = $test->subjects->first();
        $detailId = $testSubject->questions[0]['detail_id'];

        $this->save($test, $testSubject->id, $detailId, [2]);
        $this->save($test, $testSubject->id, $detailId, []);

        $response = $this->actingAs($this->user)->get(route('student.test.process', $test))->assertOk();
        $subjectsData = $response->viewData('subjectsData');

        $this->assertSame([], $subjectsData[0]['questions'][0]['user_answers']);
        $this->assertSame(0, $subjectsData[0]['answered']);
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

    /** @param  array<int, int|null>  $userAnswers */
    private function save(Test $test, int $testSubjectId, int $detailId, array $userAnswers): void
    {
        $this->actingAs($this->user)
            ->postJson(route('student.test.save', $test), [
                'subjects' => [[
                    'test_subject_id' => $testSubjectId,
                    'questions' => [[
                        'detail_id' => $detailId,
                        'user_answers' => $userAnswers,
                    ]],
                ]],
            ])->assertOk();
    }
}
