<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TestAccessType;
use App\Exports\ResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Test;
use App\Services\TestReviewService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResultController extends Controller
{
    public function __construct(private TestReviewService $review) {}

    public function index(Request $request): View
    {
        $tests = $this->filteredTests($request)
            ->with($this->eagerLoads())
            ->latest()
            ->paginate()
            ->withQueryString();

        $stats = $this->stats($request);

        $groups = Group::orderBy('title')->pluck('title', 'id');
        $subjects = Subject::orderBy('title')->pluck('title', 'id');
        $types = collect(TestAccessType::cases())->mapWithKeys(fn (TestAccessType $t) => [$t->value => $t->label()]);
        $statuses = collect(['completed' => 'Завершённые', 'in_progress' => 'Не завершённые']);

        $navigations = [route('admin.results.index') => 'Результаты'];

        return view('admin.results.index', compact(
            'tests', 'stats', 'groups', 'subjects', 'types', 'statuses', 'navigations'
        ));
    }

    public function show(Test $test): View|RedirectResponse
    {
        if (! $test->isCompleted()) {
            return redirect()->route('admin.results.index')
                ->with('error', 'Тест ещё не завершён — разбор недоступен.');
        }

        $test->load([
            'user' => fn ($q) => $q->withTrashed()->with('group'),
            'access',
            'subjects.subject',
            'subjects.part',
        ]);

        $subjectsData = $this->review->subjectsData($test, withCorrect: true);

        $navigations = [
            route('admin.results.index') => 'Результаты',
            '#' => 'Разбор — '.($test->user?->name ?? '—'),
        ];

        return view('admin.results.show', compact('test', 'subjectsData', 'navigations'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $tests = $this->filteredTests($request)
            ->with($this->eagerLoads())
            ->latest()
            ->get();

        $filename = 'edcode-results-'.now()->format('Y-m-d-Hi').'.xlsx';

        return Excel::download(new ResultsExport($tests), $filename);
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /** @return Builder<Test> */
    private function filteredTests(Request $request): Builder
    {
        return Test::query()
            ->when($request->search, function (Builder $q) use ($request) {
                $q->whereHas('user', fn (Builder $u) => $u->withTrashed()->where(function (Builder $w) use ($request) {
                    $w->where('name', 'like', "%{$request->search}%")
                        ->orWhere('login', 'like', "%{$request->search}%")
                        ->orWhere('iin', 'like', "%{$request->search}%");
                }));
            })
            ->when($request->group_id, fn (Builder $q) => $q->whereHas(
                'user', fn (Builder $u) => $u->withTrashed()->where('group_id', $request->group_id)
            ))
            ->when($request->subject_id, fn (Builder $q) => $q->whereHas(
                'subjects', fn (Builder $s) => $s->where('subject_id', $request->subject_id)
            ))
            ->when($request->type, fn (Builder $q) => $q->whereHas(
                'access', fn (Builder $a) => $a->where('type', $request->type)
            ))
            ->when($request->status === 'completed', fn (Builder $q) => $q->whereNotNull('completed_at'))
            ->when($request->status === 'in_progress', fn (Builder $q) => $q->whereNull('completed_at'))
            ->when($request->date_from, fn (Builder $q) => $q->whereDate('started_at', '>=', $request->date_from))
            ->when($request->date_to, fn (Builder $q) => $q->whereDate('started_at', '<=', $request->date_to));
    }

    /** @return array<string, mixed> */
    private function eagerLoads(): array
    {
        return [
            'user' => fn ($q) => $q->withTrashed()->with('group'),
            'access',
            'subjects.subject',
        ];
    }

    /** @return array{total: int, students: int, completed: int, averagePercent: int|null} */
    private function stats(Request $request): array
    {
        $base = $this->filteredTests($request);

        $completed = (clone $base)->whereNotNull('completed_at')->get(['total_score', 'max_score']);

        return [
            'total' => (clone $base)->count(),
            'students' => (clone $base)->distinct('user_id')->count('user_id'),
            'completed' => $completed->count(),
            'averagePercent' => $completed->isNotEmpty()
                ? (int) round($completed->avg(fn (Test $t) => $t->max_score > 0 ? $t->total_score / $t->max_score * 100 : 0))
                : null,
        ];
    }
}
