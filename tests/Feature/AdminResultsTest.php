<?php

namespace Tests\Feature;

use App\Enums\TestAccessType;
use App\Exports\ResultsExport;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\TestSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminResultsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->create();
        $this->subject = Subject::factory()->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.results.index'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_results_page(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.results.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_results_with_scores(): void
    {
        $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);

        $this->actingAs($this->admin)
            ->get(route('admin.results.index'))
            ->assertOk()
            ->assertSee($this->student->name)
            ->assertSee($this->subject->title)
            ->assertSee('15/20');
    }

    public function test_results_can_be_filtered_by_subject(): void
    {
        $otherSubject = Subject::factory()->create();
        $otherStudent = User::factory()->create();

        $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);
        $this->createCompletedTest($otherStudent, $otherSubject, score: 10, maxScore: 20);

        $this->actingAs($this->admin)
            ->get(route('admin.results.index', ['subject_id' => $this->subject->id]))
            ->assertOk()
            ->assertSee($this->student->name)
            ->assertDontSee($otherStudent->name);
    }

    public function test_results_can_be_filtered_by_status(): void
    {
        $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);

        $inProgress = User::factory()->create();
        $this->createTest($inProgress, $this->subject, completed: false);

        $this->actingAs($this->admin)
            ->get(route('admin.results.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee($this->student->name)
            ->assertDontSee($inProgress->name);
    }

    public function test_admin_can_export_results_to_excel(): void
    {
        $this->freezeTime();

        $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);

        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.results.export'))
            ->assertOk();

        Excel::assertDownloaded(
            'edcode-results-'.now()->format('Y-m-d-Hi').'.xlsx',
            function (ResultsExport $export) {
                $sheets = $export->sheets();

                return count($sheets) === 2
                    && in_array($this->subject->title, $sheets[0]->headings(), true)
                    && $sheets[0]->array()[0][1] === $this->student->name;
            }
        );
    }

    public function test_export_respects_filters(): void
    {
        $this->freezeTime();

        $otherSubject = Subject::factory()->create();
        $otherStudent = User::factory()->create();

        $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);
        $this->createCompletedTest($otherStudent, $otherSubject, score: 10, maxScore: 20);

        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.results.export', ['subject_id' => $this->subject->id]))
            ->assertOk();

        Excel::assertDownloaded(
            'edcode-results-'.now()->format('Y-m-d-Hi').'.xlsx',
            fn (ResultsExport $export) => count($export->sheets()[0]->array()) === 1
        );
    }

    public function test_admin_can_view_test_review(): void
    {
        $test = $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);

        $this->actingAs($this->admin)
            ->get(route('admin.results.show', $test))
            ->assertOk()
            ->assertSee($this->student->name)
            ->assertSee($this->subject->title);
    }

    public function test_incomplete_test_review_redirects_back_to_results(): void
    {
        $test = $this->createTest($this->student, $this->subject, completed: false);

        $this->actingAs($this->admin)
            ->get(route('admin.results.show', $test))
            ->assertRedirect(route('admin.results.index'));
    }

    public function test_student_cannot_view_admin_test_review(): void
    {
        $test = $this->createCompletedTest($this->student, $this->subject, score: 15, maxScore: 20);

        $this->actingAs($this->student)
            ->get(route('admin.results.show', $test))
            ->assertForbidden();
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function createCompletedTest(User $student, Subject $subject, int $score, int $maxScore): Test
    {
        return $this->createTest($student, $subject, completed: true, score: $score, maxScore: $maxScore);
    }

    private function createTest(User $student, Subject $subject, bool $completed, int $score = 0, int $maxScore = 0): Test
    {
        $access = TestAccess::create([
            'type' => TestAccessType::Subject,
            'user_id' => $student->id,
            'is_active' => true,
        ]);

        $test = Test::create([
            'test_access_id' => $access->id,
            'user_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(30),
            'completed_at' => $completed ? now() : null,
            'total_score' => $score,
            'max_score' => $maxScore,
        ]);

        TestSubject::create([
            'test_id' => $test->id,
            'subject_id' => $subject->id,
            'questions' => [],
            'score' => $score,
            'max_score' => $maxScore,
        ]);

        return $test;
    }
}
