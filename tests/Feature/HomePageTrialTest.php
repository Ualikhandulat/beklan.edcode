<?php

namespace Tests\Feature;

use App\Enums\PartType;
use App\Enums\TestAccessType;
use App\Models\Part;
use App\Models\Question;
use App\Models\Subject;
use App\Models\TestAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Блок пробного теста на главной странице: показывается только при наличии
 * активного пробного доступа и ведёт на регистрацию.
 */
class HomePageTrialTest extends TestCase
{
    use RefreshDatabase;

    private Subject $subject;

    private Part $nusqa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::factory()->create(['title' => 'Информатика']);
        $this->nusqa = Part::factory()->nusqa('1')->create(['subject_id' => $this->subject->id]);
        Question::factory()->one($this->subject->id, $this->nusqa->id)->create();
    }

    public function test_home_page_hides_trial_block_without_trial_access(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('register'))
            ->assertDontSee('id="trial"', false);
    }

    public function test_home_page_shows_trial_block_with_active_trial_access(): void
    {
        $this->createTrialAccess();

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('id="trial"', false);
        $response->assertSee(route('register'));
        $response->assertSee('Информатика');
        $response->assertSee(__('Пройти пробный тест'));
        $response->assertSee(__('Пробный тест бесплатно'));
    }

    public function test_home_page_hides_trial_block_when_access_is_inactive(): void
    {
        $this->createTrialAccess(['is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('register'));
    }

    public function test_home_page_hides_trial_block_when_access_is_expired(): void
    {
        $this->createTrialAccess(['expires_at' => now()->subDay()]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('register'));
    }

    /** @param array<string, mixed> $overrides */
    private function createTrialAccess(array $overrides = []): TestAccess
    {
        $access = TestAccess::create(array_merge([
            'type' => TestAccessType::Subject,
            'is_trial' => true,
            'attempts_limit' => 1,
            'duration_minutes' => 40,
        ], $overrides));

        $access->accessSubjects()->create([
            'subject_id' => $this->subject->id,
            'part_type' => PartType::Nusqa->value,
            'part_id' => $this->nusqa->id,
        ]);

        return $access;
    }
}
