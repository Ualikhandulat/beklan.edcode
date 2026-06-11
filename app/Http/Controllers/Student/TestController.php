<?php

namespace App\Http\Controllers\Student;

use App\Enums\QuestionType;
use App\Enums\TestAccessType;
use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\Question;
use App\Models\QuestionDetail;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\TestSubject;
use App\Models\User;
use App\Services\TestAssemblyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestController extends Controller
{
    public function __construct(private TestAssemblyService $assembly) {}

    /** Show the start/configure screen for a test access. */
    public function index(TestAccess $access): View|RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeAccess($access, $user);

        if (! $access->is_active) {
            return redirect()->route('student.dashboard')
                ->with('error', __('Доступ к этому тесту деактивирован.'));
        }

        // Auto-finish expired incomplete tests so they count as used attempts
        if ($access->duration_minutes) {
            $expiryThreshold = now()->subMinutes($access->duration_minutes);
            Test::where('test_access_id', $access->id)
                ->where('user_id', $user->id)
                ->whereNull('completed_at')
                ->where('started_at', '<', $expiryThreshold)
                ->get()
                ->each(fn (Test $expiredTest) => $this->assembly->score($expiredTest));
        }

        // Check attempt limit
        if ($access->attempts_limit > 0) {
            $used = Test::where('test_access_id', $access->id)
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->count();

            if ($used >= $access->attempts_limit) {
                return redirect()->route('student.dashboard')
                    ->with('error', __('Вы исчерпали все попытки для этого теста.'));
            }
        }

        // If there's an in-progress test for this access, resume it
        $active = Test::where('test_access_id', $access->id)
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if ($active) {
            $active->setRelation('access', $access);

            if (! $active->isExpired()) {
                return redirect()->route('student.test.process', $active);
            }

            $this->assembly->score($active);
        }

        $access->load('accessSubjects.subject');

        // Subjects available for elective choice (if student_chooses_subject)
        $electiveSubjects = $access->student_chooses_subject
            ? Subject::where('is_active', true)
                ->where('is_mandatory', false)
                ->orderBy('title')
                ->get(['id', 'title'])
            : collect();

        // Parts available for нұсқа/topic choice (if student_chooses_part — subject type)
        $choosableParts = collect();
        if ($access->type !== TestAccessType::Ent) {
            $cfg = $access->accessSubjects->first();
            if ($cfg && $cfg->student_chooses_part && $cfg->part_type) {
                $choosableParts = Part::where('subject_id', $cfg->subject_id)
                    ->where('type', $cfg->part_type->value)
                    ->orderBy('title')
                    ->get(['id', 'title']);
            }
        }

        // Нұсқа list for ENT student_chooses_nusqa
        $nusqaNumbers = collect();
        if ($access->student_chooses_nusqa) {
            // Нұсқа numbering is shared across subjects (Nth nұсқа, ordered by id), so any
            // configured subject works as a reference — don't assume mandatory ones exist.
            $referenceSubjectId = $access->accessSubjects->first()?->subject_id
                ?? $electiveSubjects->first()?->id;

            if ($referenceSubjectId) {
                $nusqaNumbers = Part::where('subject_id', $referenceSubjectId)
                    ->where('type', 'nusqa')
                    ->orderBy('id')
                    ->get(['id', 'title'])
                    ->map(fn ($p, $i) => ['number' => $i + 1, 'title' => $p->title]);
            }
        }

        return view('student.test.start', compact('access', 'electiveSubjects', 'choosableParts', 'nusqaNumbers'));
    }

    /** POST: Create the test session and redirect to the test UI. */
    public function start(Request $request, TestAccess $access): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeAccess($access, $user);

        if (! $access->is_active) {
            return redirect()->route('student.dashboard')
                ->with('error', __('Доступ к этому тесту деактивирован.'));
        }

        $this->validateChoices($request, $access);

        $choices = $request->only(['nusqa_number', 'elective_subject_ids', 'part_id']);

        $test = $this->assembly->build($access, $user, $choices);

        return redirect()->route('student.test.process', $test);
    }

    /** Show the test UI. */
    public function process(Test $test): View|RedirectResponse
    {
        $this->authorizeTest($test);

        if ($test->isCompleted()) {
            return redirect()->route('student.test.result', $test);
        }

        $test->load(['subjects.subject', 'subjects.part', 'access']);

        if ($test->isExpired()) {
            $this->assembly->score($test);

            return redirect()->route('student.test.result', $test);
        }

        // Build question details for the view
        $subjectsData = $this->buildSubjectsData($test);

        return view('student.test.process', compact('test', 'subjectsData'));
    }

    /** AJAX: Bulk-save all subject answers (called every 60 s and before finish). */
    public function save(Request $request, Test $test): JsonResponse
    {
        $this->authorizeTest($test);

        if ($test->isCompleted()) {
            return response()->json(['ok' => true]);
        }

        $request->validate([
            'subjects' => ['required', 'array'],
            'subjects.*.test_subject_id' => ['required', 'integer'],
            'subjects.*.questions' => ['required', 'array'],
            'subjects.*.questions.*.detail_id' => ['required', 'integer'],
            'subjects.*.questions.*.user_answers' => ['present', 'array'],
            'subjects.*.questions.*.user_answers.*' => ['nullable', 'integer'],
        ]);

        $testSubjectMap = $test->subjects->keyBy('id');

        foreach ($request->subjects as $subjectData) {
            $testSubject = $testSubjectMap->get($subjectData['test_subject_id']);

            if (! $testSubject) {
                continue;
            }

            $questions = $testSubject->questions;
            $positionByDetailId = array_flip(array_column($questions, 'detail_id'));

            foreach ($subjectData['questions'] as $entry) {
                $pos = $positionByDetailId[$entry['detail_id']] ?? null;

                if ($pos === null) {
                    continue;
                }

                $answers = array_values(
                    array_map('intval', array_filter($entry['user_answers'], fn ($a) => $a !== null))
                );
                $countAnswers = (int) ($questions[$pos]['count_answers'] ?? PHP_INT_MAX);
                $questions[$pos]['user_answers'] = array_slice($answers, 0, $countAnswers);
            }

            $testSubject->update(['questions' => $questions]);
        }

        return response()->json(['ok' => true]);
    }

    /** POST: Finish the test, calculate scores. */
    public function finish(Test $test): JsonResponse
    {
        $this->authorizeTest($test);

        if ($test->isCompleted()) {
            return response()->json(['redirect' => route('student.test.result', $test)]);
        }

        $this->assembly->score($test);

        return response()->json(['redirect' => route('student.test.result', $test)]);
    }

    /** Show detailed review of all questions with correct answers. */
    public function detail(Test $test): View|RedirectResponse
    {
        $this->authorizeTest($test);

        if (! $test->isCompleted()) {
            return redirect()->route('student.test.result', $test);
        }

        $test->load(['subjects.subject', 'subjects.part', 'access']);

        $subjectsData = $this->buildSubjectsData($test, withCorrect: true);

        return view('student.test.detail', compact('test', 'subjectsData'));
    }

    /** Show test results. */
    public function result(Test $test): View|RedirectResponse
    {
        $this->authorizeTest($test);

        if (! $test->isCompleted()) {
            return redirect()->route('student.test.process', $test);
        }

        $test->load(['subjects.subject', 'subjects.part']);

        // The result page only shows per-subject scores, not question details —
        // no need to pull every question/detail like buildSubjectsData() does.
        $subjectsData = $test->subjects->map(fn (TestSubject $testSubject) => [
            'subject' => $testSubject->subject,
            'part' => $testSubject->part,
            'score' => $testSubject->score,
            'max_score' => $testSubject->max_score,
        ])->all();

        return view('student.test.result', compact('test', 'subjectsData'));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function authorizeAccess(TestAccess $access, User $user): void
    {
        $allowed = $access->user_id === $user->id
            || ($user->group_id && $access->group_id === $user->group_id);

        abort_if(! $allowed, 403);
    }

    private function authorizeTest(Test $test): void
    {
        abort_if($test->user_id !== auth()->id(), 403);
    }

    /** Make sure the student has made all choices required by the access config before assembling the test. */
    private function validateChoices(Request $request, TestAccess $access): void
    {
        if ($access->type === TestAccessType::Ent) {
            if ($access->student_chooses_subject) {
                $request->validate([
                    'elective_subject_ids' => ['required', 'array', 'size:2'],
                    'elective_subject_ids.*' => ['required', 'distinct', 'exists:subjects,id'],
                ], [
                    'elective_subject_ids.required' => __('Выберите профильные предметы.'),
                    'elective_subject_ids.size' => __('Нужно выбрать ровно 2 предмета.'),
                    'elective_subject_ids.*.required' => __('Выберите профильные предметы.'),
                    'elective_subject_ids.*.distinct' => __('Предметы не должны повторяться.'),
                ]);
            }

            if ($access->student_chooses_nusqa) {
                $request->validate([
                    'nusqa_number' => ['required', 'integer', 'min:1'],
                ], [
                    'nusqa_number.required' => __('Выберите нұсқа.'),
                ]);
            }

            return;
        }

        $cfg = $access->accessSubjects->first();

        if ($cfg && $cfg->student_chooses_part && $cfg->part_type) {
            $request->validate([
                'part_id' => ['required', 'integer', Rule::exists('parts', 'id')->where('subject_id', $cfg->subject_id)],
            ], [
                'part_id.required' => __('Выберите раздел.'),
            ]);
        }
    }

    /**
     * Build enriched subjects array for the view.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSubjectsData(Test $test, bool $withCorrect = false): array
    {
        $detailIds = $test->subjects
            ->flatMap(fn ($testSubject) => collect($testSubject->questions)->pluck('detail_id'))
            ->unique();

        // withTrashed: a question/detail may have been soft-deleted by an admin after
        // the test was assembled — the student must still be able to finish/review it.
        $details = QuestionDetail::withTrashed()->whereIn('id', $detailIds)
            ->get()
            ->keyBy('id');

        // Load parent Question models separately to avoid the `question` column/relation name clash
        $questionModels = Question::withTrashed()->whereIn('id', $details->pluck('question_id')->unique())
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($test->subjects as $testSubject) {
            $questions = [];
            foreach ($testSubject->questions as $idx => $q) {
                $detail = $details->get($q['detail_id']);
                $questionModel = $detail ? $questionModels->get($detail->question_id) : null;

                if (! $detail || ! $questionModel) {
                    continue;
                }

                $vars = [];
                for ($i = 1; $i <= 10; $i++) {
                    $val = $detail->{"var{$i}"};
                    if ($val !== null && $val !== '') {
                        $vars[] = $val;
                    }
                }

                // Apply the stored shuffle order so each student sees options in a unique order
                $varOrder = $q['var_order'] ?? null;
                if ($varOrder !== null && count($varOrder) === count($vars)) {
                    $shuffledVars = [];
                    foreach ($varOrder as $originalIdx) {
                        $shuffledVars[] = $vars[$originalIdx] ?? null;
                    }
                    $vars = $shuffledVars;
                }

                // Remap correct answer original-indices to display-indices so they align
                // with user_answers (which are stored in display order after shuffling).
                $correct = null;
                if ($withCorrect) {
                    $originalCorrect = $detail->answers ?? [];
                    if ($varOrder !== null) {
                        $reverseOrder = array_flip($varOrder);
                        $correct = array_values(array_filter(
                            array_map(fn ($origIdx) => $reverseOrder[$origIdx] ?? null, $originalCorrect),
                            fn ($v) => $v !== null
                        ));
                    } else {
                        $correct = $originalCorrect;
                    }
                }

                $questions[] = [
                    'index' => $idx,
                    'detail_id' => $detail->id,
                    'context' => $questionModel->type === QuestionType::IS_GROUP ? $questionModel->text : null,
                    'text' => $detail->question ?? $questionModel->text,
                    'type' => $questionModel->type,
                    'count_answers' => $questionModel->count_answers,
                    'vars' => $vars,
                    'user_answers' => $q['user_answers'],
                    'is_right' => $q['is_right'],
                    'correct' => $correct,
                ];
            }

            $result[] = [
                'test_subject_id' => $testSubject->id,
                'subject' => $testSubject->subject,
                'part' => $testSubject->part,
                'questions' => $questions,
                'score' => $testSubject->score,
                'max_score' => $testSubject->max_score,
                'answered' => collect($questions)->filter(function ($q) {
                    $answers = $q['user_answers'] ?? [];

                    return ! empty($answers) && collect($answers)->every(fn ($a) => $a !== null);
                })->count(),
            ];
        }

        return $result;
    }
}
