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
 * Доступ по предмету с типом «Нұсқа»: админ отмечает подмножество нұсқа (part_ids),
 * студент при старте выбирает одну только из отмеченных.
 */
class NusqaSubsetChoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    private Subject $subject;

    /** @var Collection<int, Part> */
    private $nusqas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->student()->create();
        $this->subject = Subject::factory()->create();

        // Title нұсқа в реальных данных — просто номер («1», «2»…)
        $this->nusqas = collect(range(1, 4))->map(function (int $i) {
            $part = Part::factory()->nusqa((string) $i)->create(['subject_id' => $this->subject->id]);
            Question::factory()->one($this->subject->id, $part->id)->create();

            return $part;
        });
    }

    public function test_admin_stores_marked_nusqa_subset(): void
    {
        $marked = [$this->nusqas[0]->id, $this->nusqas[2]->id];

        $this->actingAs($this->admin)
            ->post(route('admin.test-accesses.store'), $this->accessPayload($marked))
            ->assertRedirect(route('admin.test-accesses.index'));

        $cfg = TestAccess::latest('id')->first()->accessSubjects()->first();

        $this->assertSame($marked, $cfg->part_ids);
        $this->assertTrue($cfg->student_chooses_part);
    }

    public function test_part_ids_from_other_subject_are_dropped(): void
    {
        $foreignPart = Part::factory()->nusqa('99')->create();

        $this->actingAs($this->admin)
            ->post(route('admin.test-accesses.store'), $this->accessPayload([$this->nusqas[0]->id, $foreignPart->id]))
            ->assertRedirect(route('admin.test-accesses.index'));

        $cfg = TestAccess::latest('id')->first()->accessSubjects()->first();

        $this->assertSame([$this->nusqas[0]->id], $cfg->part_ids);
    }

    public function test_start_page_shows_only_marked_nusqas(): void
    {
        $access = $this->createAccess([$this->nusqas[0]->id, $this->nusqas[2]->id]);

        $response = $this->actingAs($this->student)
            ->get(route('student.test.index', $access))
            ->assertOk();

        $response->assertSee('Нұсқа 1');
        $response->assertSee('Нұсқа 3');
        $response->assertDontSee('Нұсқа 2');
        $response->assertDontSee('Нұсқа 4');
    }

    public function test_student_cannot_start_with_unmarked_nusqa(): void
    {
        $access = $this->createAccess([$this->nusqas[0]->id, $this->nusqas[2]->id]);

        $this->actingAs($this->student)
            ->post(route('student.test.start', $access), ['part_id' => $this->nusqas[1]->id])
            ->assertSessionHasErrors('part_id');

        $this->assertSame(0, Test::count());
    }

    public function test_student_starts_with_marked_nusqa(): void
    {
        $access = $this->createAccess([$this->nusqas[0]->id, $this->nusqas[2]->id]);

        $this->actingAs($this->student)
            ->post(route('student.test.start', $access), ['part_id' => $this->nusqas[2]->id])
            ->assertRedirect();

        $test = Test::firstOrFail();

        $this->assertSame($this->nusqas[2]->id, $test->subjects()->first()->part_id);
    }

    public function test_empty_subset_keeps_all_nusqas_choosable(): void
    {
        $access = $this->createAccess([]);

        $response = $this->actingAs($this->student)
            ->get(route('student.test.index', $access))
            ->assertOk();

        foreach (range(1, 4) as $i) {
            $response->assertSee("Нұсқа {$i}");
        }

        $this->actingAs($this->student)
            ->post(route('student.test.start', $access), ['part_id' => $this->nusqas[1]->id])
            ->assertRedirect();

        $this->assertSame($this->nusqas[1]->id, Test::firstOrFail()->subjects()->first()->part_id);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * @param  int[]  $partIds
     * @return array<string, mixed>
     */
    private function accessPayload(array $partIds): array
    {
        return [
            'type' => TestAccessType::Subject->value,
            'user_id' => $this->student->id,
            'attempts_limit' => 1,
            'subject' => [
                'subject_id' => $this->subject->id,
                'part_type' => PartType::Nusqa->value,
                'part_id' => '',
                'part_ids' => $partIds,
                'student_chooses_part' => 1,
            ],
        ];
    }

    /** @param int[] $partIds */
    private function createAccess(array $partIds): TestAccess
    {
        $access = TestAccess::create([
            'type' => TestAccessType::Subject,
            'user_id' => $this->student->id,
            'attempts_limit' => 1,
        ]);

        $access->accessSubjects()->create([
            'subject_id' => $this->subject->id,
            'part_type' => PartType::Nusqa->value,
            'part_ids' => $partIds ?: null,
            'student_chooses_part' => true,
        ]);

        return $access;
    }
}
