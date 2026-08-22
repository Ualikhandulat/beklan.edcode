<?php

namespace Tests\Feature;

use App\Enums\PartType;
use App\Enums\TestAccessType;
use App\Models\Part;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Пробный доступ (is_trial): виден только пользователям с has_trial_access,
 * пробный нұсқа скрыт из списков выбора обычных доступов, активный пробный
 * доступ может быть только один, админ открывает его ученику вручную.
 */
class TrialAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Subject $subject;

    /** @var Collection<int, Part> */
    private $nusqas;

    private TestAccess $trialAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->subject = Subject::factory()->create();

        $this->nusqas = collect(range(1, 3))->map(function (int $i) {
            $part = Part::factory()->nusqa((string) $i)->create(['subject_id' => $this->subject->id]);
            Question::factory()->one($this->subject->id, $part->id)->create();

            return $part;
        });

        // Нұсқа 3 — пробный
        $this->trialAccess = TestAccess::create([
            'type' => TestAccessType::Subject,
            'is_trial' => true,
            'attempts_limit' => 1,
        ]);
        $this->trialAccess->accessSubjects()->create([
            'subject_id' => $this->subject->id,
            'part_type' => PartType::Nusqa->value,
            'part_id' => $this->nusqas[2]->id,
        ]);
    }

    public function test_trial_access_is_visible_to_user_with_trial_flag(): void
    {
        $user = User::factory()->student()->create(['has_trial_access' => true]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('student.test.index', $this->trialAccess));

        $this->actingAs($user)
            ->get(route('student.test.index', $this->trialAccess))
            ->assertOk();
    }

    public function test_trial_access_is_hidden_from_regular_student(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee(route('student.test.index', $this->trialAccess));

        $this->actingAs($student)
            ->get(route('student.test.index', $this->trialAccess))
            ->assertForbidden();
    }

    public function test_trial_nusqa_is_hidden_from_paid_choice_list(): void
    {
        $student = User::factory()->student()->create();

        $paidAccess = TestAccess::create([
            'type' => TestAccessType::Subject,
            'user_id' => $student->id,
            'attempts_limit' => 1,
        ]);
        $paidAccess->accessSubjects()->create([
            'subject_id' => $this->subject->id,
            'part_type' => PartType::Nusqa->value,
            'student_chooses_part' => true,
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.test.index', $paidAccess))
            ->assertOk();

        $response->assertSee('Нұсқа 1');
        $response->assertSee('Нұсқа 2');
        $response->assertDontSee('Нұсқа 3');

        $this->actingAs($student)
            ->post(route('student.test.start', $paidAccess), ['part_id' => $this->nusqas[2]->id])
            ->assertSessionHasErrors('part_id');

        $this->assertSame(0, Test::count());
    }

    public function test_trial_user_can_start_trial_test(): void
    {
        $user = User::factory()->student()->create(['has_trial_access' => true]);

        $this->actingAs($user)
            ->post(route('student.test.start', $this->trialAccess))
            ->assertRedirect();

        $test = Test::firstOrFail();

        $this->assertSame($user->id, $test->user_id);
        $this->assertSame($this->nusqas[2]->id, $test->subjects()->first()->part_id);
    }

    public function test_admin_grants_trial_access_to_student_without_tests(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.users.grant-trial', $student))
            ->assertRedirect(route('admin.users.edit', $student))
            ->assertSessionHas('success');

        $this->assertTrue($student->refresh()->has_trial_access);
    }

    public function test_admin_cannot_grant_trial_access_to_student_with_tests(): void
    {
        $student = User::factory()->student()->create();

        Test::create([
            'test_access_id' => $this->trialAccess->id,
            'user_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.users.grant-trial', $student))
            ->assertSessionHas('error');

        $this->assertFalse($student->refresh()->has_trial_access);
    }

    public function test_admin_cannot_grant_trial_access_twice(): void
    {
        $student = User::factory()->student()->create(['has_trial_access' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.users.grant-trial', $student))
            ->assertSessionHas('error');
    }

    public function test_trial_access_is_hidden_from_admin_accesses_list(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.test-accesses.index'))
            ->assertOk()
            ->assertDontSee(route('admin.test-accesses.edit', $this->trialAccess));
    }

    public function test_regular_access_still_requires_user_or_group(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.test-accesses.store'), [
                'type' => TestAccessType::Subject->value,
                'attempts_limit' => 1,
                'subject' => ['subject_id' => $this->subject->id],
            ])
            ->assertSessionHasErrors(['user_id', 'group_id']);
    }
}
