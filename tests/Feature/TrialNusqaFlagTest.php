<?php

namespace Tests\Feature;

use App\Enums\PartType;
use App\Enums\TestAccessType;
use App\Models\Part;
use App\Models\Subject;
use App\Models\TestAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Галочка «Пробный нұсқа» в форме нұсқа: автоматически создаёт и перенастраивает
 * единый пробный доступ, снятие галочки его деактивирует.
 */
class TrialNusqaFlagTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->subject = Subject::factory()->create();
    }

    public function test_marking_nusqa_as_trial_creates_trial_access(): void
    {
        $part = Part::factory()->nusqa('1')->create(['subject_id' => $this->subject->id]);

        $this->updatePart($part, isTrial: 1)
            ->assertRedirect(route('admin.subjects.show', $this->subject));

        $access = TestAccess::where('is_trial', true)->firstOrFail();

        $this->assertTrue($access->is_active);
        $this->assertSame(TestAccessType::Subject, $access->type);
        $this->assertSame(1, $access->attempts_limit);
        $this->assertNull($access->user_id);
        $this->assertNull($access->group_id);

        $cfg = $access->accessSubjects()->first();
        $this->assertSame($part->id, $cfg->part_id);
        $this->assertSame($this->subject->id, $cfg->subject_id);
    }

    public function test_marking_another_nusqa_repoints_existing_trial_access(): void
    {
        $first = Part::factory()->nusqa('1')->create(['subject_id' => $this->subject->id]);
        $second = Part::factory()->nusqa('2')->create(['subject_id' => $this->subject->id]);

        $this->updatePart($first, isTrial: 1);
        $this->updatePart($second, isTrial: 1);

        $this->assertSame(1, TestAccess::where('is_trial', true)->count());

        $access = TestAccess::where('is_trial', true)->firstOrFail();
        $this->assertTrue($access->is_active);
        $this->assertSame($second->id, $access->accessSubjects()->first()->part_id);
    }

    public function test_unmarking_trial_nusqa_deactivates_trial_access(): void
    {
        $part = Part::factory()->nusqa('1')->create(['subject_id' => $this->subject->id]);

        $this->updatePart($part, isTrial: 1);
        $this->updatePart($part, isTrial: 0);

        $this->assertFalse(TestAccess::where('is_trial', true)->firstOrFail()->is_active);
    }

    public function test_creating_nusqa_with_trial_flag_assigns_it(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.subjects.parts.store', $this->subject), [
                'title' => '7',
                'type' => PartType::Nusqa->value,
                'is_trial' => 1,
            ])
            ->assertRedirect(route('admin.subjects.show', $this->subject));

        $part = Part::where('title', '7')->firstOrFail();
        $access = TestAccess::where('is_trial', true)->firstOrFail();

        $this->assertSame($part->id, $access->accessSubjects()->first()->part_id);
    }

    public function test_saving_regular_nusqa_does_not_touch_foreign_trial_access(): void
    {
        $trialPart = Part::factory()->nusqa('1')->create(['subject_id' => $this->subject->id]);
        $regularPart = Part::factory()->nusqa('2')->create(['subject_id' => $this->subject->id]);

        $this->updatePart($trialPart, isTrial: 1);
        $this->updatePart($regularPart, isTrial: 0);

        $access = TestAccess::where('is_trial', true)->firstOrFail();
        $this->assertTrue($access->is_active);
        $this->assertSame($trialPart->id, $access->accessSubjects()->first()->part_id);
    }

    private function updatePart(Part $part, int $isTrial): TestResponse
    {
        return $this->actingAs($this->admin)
            ->put(route('admin.subjects.parts.update', [$this->subject, $part]), [
                'title' => $part->title,
                'type' => PartType::Nusqa->value,
                'is_trial' => $isTrial,
            ]);
    }
}
