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
use App\Services\TestReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет страницу РАЗБОРА (detail) для вопросов на «соответствие»: то, что студент
 * фактически видит — подсвеченный «Верный ответ» совпадает по ТЕКСТУ с каноническим,
 * выбранный вариант показан верно, а баллы/статус (полный/частичный/неверный) согласованы.
 *
 * Отвечаем НЕ по позициям, а «по тексту» — находим в перемешанных вариантах тот, чей текст
 * равен эталону (как реальный студент, который знает правильный ответ), — поэтому тест ловит
 * любой рассинхрон между перемешиванием вариантов и ремаппингом правильного ответа.
 */
class MatchReviewTest extends TestCase
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

    public function test_review_highlights_the_truly_correct_option_by_text_and_scores_full(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        $detail = $this->matchDetail($test);
        // Эталонные тексты: var5 — верный для пары 1, var6 — верный для пары 2.
        $correctText = [$detail->var5, $detail->var6];

        // «Студент» выбирает по тексту: ищет в показанных (перемешанных) вариантах нужный.
        $review = $this->review($test);
        $q = $this->matchQuestion($review);
        $userAnswers = [
            $this->displayPositionOfText($q['vars'], $correctText[0]),
            $this->displayPositionOfText($q['vars'], $correctText[1]),
        ];

        $this->saveAndFinish($test, $q['detail_id'], $userAnswers);

        $review = $this->review($test);
        $q = $this->matchQuestion($review);

        // Балл и статус
        $this->assertSame(2, $q['points'], 'Оба соответствия верны — 2 балла.');
        $this->assertSame(2, $q['max_points']);
        $this->assertTrue($q['is_right']);

        // Подсвеченный «верный ответ» по тексту совпадает с эталоном (по обеим парам).
        $this->assertSame($correctText[0], $q['vars'][$q['correct'][0] - 1], 'Верный вариант пары 1 показан неверно.');
        $this->assertSame($correctText[1], $q['vars'][$q['correct'][1] - 1], 'Верный вариант пары 2 показан неверно.');

        // Выбор студента совпадает с верным — обе пары зелёные.
        $this->assertSame($q['correct'][0], $q['user_answers'][0]);
        $this->assertSame($q['correct'][1], $q['user_answers'][1]);
    }

    public function test_review_partial_match_keeps_correct_answer_text_and_marks_partial(): void
    {
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        $detail = $this->matchDetail($test);
        $correctText = [$detail->var5, $detail->var6];
        $distractorText = $detail->var7; // дистрактор — заведомо неверный

        $review = $this->review($test);
        $q = $this->matchQuestion($review);

        // Пара 1 — верно (по тексту var5), пара 2 — неверно (выбрали дистрактор var7).
        $userAnswers = [
            $this->displayPositionOfText($q['vars'], $correctText[0]),
            $this->displayPositionOfText($q['vars'], $distractorText),
        ];

        $this->saveAndFinish($test, $q['detail_id'], $userAnswers);

        $review = $this->review($test);
        $q = $this->matchQuestion($review);

        // Частичный балл и статус
        $this->assertSame(1, $q['points'], 'Одна пара верна — 1 балл.');
        $this->assertSame(2, $q['max_points']);

        // Несмотря на ошибку студента, ВЕРНЫЙ ответ обеих пар показан по эталонному тексту.
        $this->assertSame($correctText[0], $q['vars'][$q['correct'][0] - 1]);
        $this->assertSame($correctText[1], $q['vars'][$q['correct'][1] - 1]);

        // Пара 1 совпала, пара 2 — нет.
        $this->assertSame($q['correct'][0], $q['user_answers'][0]);
        $this->assertNotSame($q['correct'][1], $q['user_answers'][1]);
        // Выбранный по паре 2 вариант — это дистрактор (по тексту).
        $this->assertSame($distractorText, $q['vars'][$q['user_answers'][1] - 1]);
    }

    public function test_review_stays_coherent_when_var_order_length_is_corrupt(): void
    {
        // Прод-сценарий: у части тестов var_order мог сохраниться «битым» (другой длины, чем
        // число вариантов) — из-за старых данных или правок. Тогда перемешивание вариантов и
        // ремаппинг правильного ответа ОБЯЗАНЫ включаться/выключаться синхронно: иначе варианты
        // остаются в исходном порядке, а «верный ответ» ремапится в порядок отображения — и
        // разбор подсвечивает не тот вариант. Этот тест ловит именно такой рассинхрон.
        Question::factory()->match($this->subject->id, $this->part->id)->create();
        $test = $this->buildTest();

        $detail = $this->matchDetail($test);
        $correctText = [$detail->var5, $detail->var6]; // эталон для пары 1 и пары 2

        // Портим длину var_order, НЕ трогая сами варианты (их 6, делаем var_order из 5 значений).
        $testSubject = $test->subjects->first();
        $questions = $testSubject->questions;
        $questions[0]['var_order'] = [1, 2, 5, 3, 6]; // длина 5 != 6
        $testSubject->update(['questions' => $questions]);

        $review = $this->review($test);
        $q = $this->matchQuestion($review);

        // vars и correct остаются в одной (исходной) системе координат — подсвеченный верный
        // вариант по тексту по-прежнему равен эталону, а не случайному из-за рассинхрона.
        $this->assertSame($correctText[0], $q['vars'][$q['correct'][0] - 1], 'Верный вариант пары 1 рассинхронизирован.');
        $this->assertSame($correctText[1], $q['vars'][$q['correct'][1] - 1], 'Верный вариант пары 2 рассинхронизирован.');
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

    private function matchDetail(Test $test): QuestionDetail
    {
        $detailId = $test->subjects->first()->questions[0]['detail_id'];

        return QuestionDetail::findOrFail($detailId);
    }

    /**
     * Вызывает TestReviewService::subjectsData (withCorrect: true) — ровно те данные, что
     * страница разбора отдаёт во фронтенд.
     *
     * @return array<int, array<string, mixed>>
     */
    private function review(Test $test): array
    {
        $test->refresh()->load(['subjects.subject', 'subjects.part', 'access']);

        return app(TestReviewService::class)->subjectsData($test, withCorrect: true);
    }

    /** @param array<int, array<string, mixed>> $review */
    private function matchQuestion(array $review): array
    {
        return $review[0]['questions'][0];
    }

    /** @param string[] $vars 1-based display position варианта, чей текст равен $text. */
    private function displayPositionOfText(array $vars, string $text): int
    {
        $idx = array_search($text, $vars, true);
        $this->assertNotFalse($idx, "Текст «{$text}» не найден среди показанных вариантов.");

        return $idx + 1;
    }

    /** @param array<int, int|null> $userAnswers */
    private function saveAndFinish(Test $test, int $detailId, array $userAnswers): void
    {
        $testSubject = $test->subjects->first();

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
