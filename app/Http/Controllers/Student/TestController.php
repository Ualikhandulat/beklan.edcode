<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\Question;
use App\Models\QuestionDetail;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\User;
use App\Services\TestAssemblyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestController extends Controller
{
    public function __construct(private TestAssemblyService $assembly) {}

    /** Show the start/configure screen for a test access. */
    public function start(TestAccess $access): View|RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeAccess($access, $user);

        // Check attempt limit
        if ($access->attempts_limit > 0) {
            $used = Test::where('test_access_id', $access->id)
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->count();

            if ($used >= $access->attempts_limit) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'Вы исчерпали все попытки для этого теста.');
            }
        }

        // If there's an in-progress test for this access, resume it
        $active = Test::where('test_access_id', $access->id)
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if ($active && ! $active->isExpired()) {
            return redirect()->route('student.test.show', $active);
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
        if (! $access->type->value === 'ent') {
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
            // Derive available нұсқа numbers from mandatory subjects
            $mandatoryId = Subject::where('is_mandatory', true)->value('id');
            $nusqaNumbers = Part::where('subject_id', $mandatoryId)
                ->where('type', 'nusqa')
                ->orderBy('id')
                ->get(['id', 'title'])
                ->map(fn ($p, $i) => ['number' => $i + 1, 'title' => $p->title]);
        }

        return view('student.test.start', compact('access', 'electiveSubjects', 'choosableParts', 'nusqaNumbers'));
    }

    /** POST: Create the test session and redirect to the test UI. */
    public function begin(Request $request, TestAccess $access): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeAccess($access, $user);

        $choices = $request->only(['nusqa_number', 'elective_subject_ids', 'part_id']);

        $test = $this->assembly->build($access, $user, $choices);

        return redirect()->route('student.test.show', $test);
    }

    /** Show the test UI. */
    public function show(Test $test): View|RedirectResponse
    {
        $this->authorizeTest($test);

        if ($test->isCompleted()) {
            return redirect()->route('student.test.results', $test);
        }

        if ($test->isExpired()) {
            $this->assembly->score($test);

            return redirect()->route('student.test.results', $test);
        }

        $test->load(['subjects.subject', 'subjects.part', 'access']);

        // Build question details for the view
        $subjectsData = $this->buildSubjectsData($test);

        return view('student.test.show', compact('test', 'subjectsData'));
    }

    /** AJAX: Save answer for a single question. */
    public function answer(Request $request, Test $test): JsonResponse
    {
        $this->authorizeTest($test);

        if ($test->isCompleted() || $test->isExpired()) {
            return response()->json(['error' => 'Test is already finished.'], 422);
        }

        $request->validate([
            'test_subject_id' => ['required', 'integer'],
            'detail_id' => ['required', 'integer'],
            'user_answers' => ['required', 'array'],
            'user_answers.*' => ['integer'],
        ]);

        $testSubject = $test->subjects()->findOrFail($request->test_subject_id);
        $questions = $testSubject->questions;

        foreach ($questions as &$q) {
            if ((int) $q['detail_id'] === (int) $request->detail_id) {
                $q['user_answers'] = array_values(array_map('intval', $request->user_answers));
                break;
            }
        }

        unset($q);

        $testSubject->update(['questions' => $questions]);

        return response()->json(['ok' => true]);
    }

    /** POST: Finish the test, calculate scores. */
    public function finish(Test $test): JsonResponse
    {
        $this->authorizeTest($test);

        if ($test->isCompleted()) {
            return response()->json(['redirect' => route('student.test.results', $test)]);
        }

        $this->assembly->score($test);

        return response()->json(['redirect' => route('student.test.results', $test)]);
    }

    /** Show test results. */
    public function results(Test $test): View|RedirectResponse
    {
        $this->authorizeTest($test);

        if (! $test->isCompleted()) {
            return redirect()->route('student.test.show', $test);
        }

        $test->load(['subjects.subject', 'subjects.part', 'access']);

        $subjectsData = $this->buildSubjectsData($test, withCorrect: true);

        return view('student.test.results', compact('test', 'subjectsData'));
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

    /**
     * Build enriched subjects array for the view.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSubjectsData(Test $test, bool $withCorrect = false): array
    {
        $result = [];

        foreach ($test->subjects as $testSubject) {
            $detailIds = collect($testSubject->questions)->pluck('detail_id');

            $details = QuestionDetail::whereIn('id', $detailIds)
                ->get()
                ->keyBy('id');

            // Load parent Question models separately to avoid the `question` column/relation name clash
            $questionModels = Question::whereIn('id', $details->pluck('question_id'))
                ->get()
                ->keyBy('id');

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

                $questions[] = [
                    'index' => $idx,
                    'detail_id' => $detail->id,
                    'text' => $detail->question ?? $questionModel->text,
                    'type' => $questionModel->type,
                    'count_answers' => $questionModel->count_answers,
                    'vars' => $vars,
                    'user_answers' => $q['user_answers'],
                    'is_right' => $q['is_right'],
                    'correct' => $withCorrect ? $detail->answers : null,
                ];
            }

            $result[] = [
                'test_subject_id' => $testSubject->id,
                'subject' => $testSubject->subject,
                'part' => $testSubject->part,
                'questions' => $questions,
                'score' => $testSubject->score,
                'max_score' => $testSubject->max_score,
                'answered' => collect($questions)->filter(fn ($q) => ! empty($q['user_answers']))->count(),
            ];
        }

        return $result;
    }
}
