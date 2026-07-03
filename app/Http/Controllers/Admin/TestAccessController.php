<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartType;
use App\Enums\Role;
use App\Enums\TestAccessType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestAccessRequest;
use App\Http\Requests\Admin\UpdateTestAccessRequest;
use App\Models\Group;
use App\Models\Part;
use App\Models\Subject;
use App\Models\TestAccess;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class TestAccessController extends Controller
{
    public function index(Request $request): View
    {
        $accesses = TestAccess::with(['user', 'group', 'accessSubjects.subject', 'accessSubjects.part'])
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->target === 'user', fn ($q) => $q->whereNotNull('user_id'))
            ->when($request->target === 'group', fn ($q) => $q->whereNotNull('group_id'))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
                        ->orWhereHas('group', fn ($q) => $q->where('title', 'like', "%{$request->search}%"));
                });
            })
            ->latest()
            ->paginate()
            ->withQueryString();

        $navigations = [route('admin.test-accesses.index') => 'Доступы'];

        return view('admin.test-accesses.index', compact('accesses', 'navigations'));
    }

    public function results(TestAccess $testAccess): View
    {
        $testAccess->load(['user', 'group.users', 'accessSubjects.subject']);

        $tests = $testAccess->tests()
            ->with('user')
            ->latest()
            ->paginate()
            ->withQueryString();

        $assignedCount = $testAccess->user_id
            ? 1
            : $testAccess->group->users->count();

        $startedCount = $testAccess->tests()->whereNotNull('started_at')->distinct('user_id')->count('user_id');
        $completedTests = $testAccess->tests()->whereNotNull('completed_at')->get(['user_id', 'total_score', 'max_score']);
        $completedCount = $completedTests->unique('user_id')->count();

        $averagePercent = $completedTests->isNotEmpty()
            ? round($completedTests->avg(fn ($t) => $t->max_score > 0 ? $t->total_score / $t->max_score * 100 : 0))
            : null;

        $navigations = [
            route('admin.test-accesses.index') => 'Доступы',
            '#' => 'Результаты — '.$testAccess->targetLabel(),
        ];

        return view('admin.test-accesses.results', compact(
            'testAccess', 'tests', 'assignedCount', 'startedCount', 'completedCount', 'averagePercent', 'navigations'
        ));
    }

    public function create(): View
    {
        return view('admin.test-accesses.form', array_merge($this->formData(), [
            'access' => new TestAccess,
            'action' => route('admin.test-accesses.store'),
            'method' => 'POST',
            'navigations' => [route('admin.test-accesses.index') => 'Доступы', '#' => 'Открыть доступ'],
        ]));
    }

    public function store(StoreTestAccessRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $access = TestAccess::create(Arr::except($data, ['nusqa_mode', 'elective', 'subject']));

        $this->syncSubjects($access, $data);

        return redirect()->route('admin.test-accesses.index')->with('success', 'Доступ открыт.');
    }

    public function edit(TestAccess $testAccess): View
    {
        $testAccess->load('accessSubjects.subject');

        return view('admin.test-accesses.form', array_merge($this->formData($testAccess), [
            'access' => $testAccess,
            'action' => route('admin.test-accesses.update', $testAccess),
            'method' => 'PUT',
            'navigations' => [route('admin.test-accesses.index') => 'Доступы', '#' => $testAccess->targetLabel()],
        ]));
    }

    public function update(UpdateTestAccessRequest $request, TestAccess $testAccess): RedirectResponse
    {
        $data = $request->validated();

        $testAccess->update(Arr::except($data, ['nusqa_mode', 'elective', 'subject']));
        $testAccess->accessSubjects()->delete();
        $this->syncSubjects($testAccess, $data);

        return redirect()->route('admin.test-accesses.index')->with('success', 'Доступ обновлён.');
    }

    public function toggleActive(TestAccess $testAccess): RedirectResponse
    {
        $testAccess->update(['is_active' => ! $testAccess->is_active]);

        $message = $testAccess->is_active ? 'Доступ активирован.' : 'Доступ деактивирован.';

        return redirect()->route('admin.test-accesses.index')->with('success', $message);
    }

    public function destroy(TestAccess $testAccess): RedirectResponse
    {
        if ($testAccess->tests()->exists()) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить: по этому доступу уже есть начатые тесты.');
        }

        $testAccess->delete();

        return redirect()->route('admin.test-accesses.index')->with('success', 'Доступ удалён.');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    private function syncSubjects(TestAccess $access, array $data): void
    {
        if ($access->type === TestAccessType::Ent) {
            // Always include all 3 mandatory subjects (no part config — handled globally)
            $mandatoryIds = Subject::where('is_mandatory', true)->pluck('id');
            foreach ($mandatoryIds as $id) {
                $access->accessSubjects()->create(['subject_id' => $id]);
            }

            // Add chosen elective subjects
            if (! $access->student_chooses_subject) {
                foreach ($data['elective'] ?? [] as $slot) {
                    $subjectId = $slot['subject_id'] ?? null;
                    if ($subjectId) {
                        $access->accessSubjects()->create(['subject_id' => $subjectId]);
                    }
                }
            }
        } else {
            $cfg = $data['subject'];
            $studentChoosesPart = (bool) ($cfg['student_chooses_part'] ?? false);

            $access->accessSubjects()->create([
                'subject_id' => $cfg['subject_id'],
                'part_type' => $cfg['part_type'] ?: null,
                'part_id' => $cfg['part_id'] ?: null,
                'part_ids' => $this->allowedNusqaPartIds($cfg, $studentChoosesPart),
                'student_chooses_part' => $studentChoosesPart,
            ]);
        }
    }

    /**
     * Отмеченные админом нұсқа, из которых студент выбирает одну при старте.
     * Актуально только для типа «Нұсқа» с выбором студента; пустой список = все нұсқа.
     *
     * @param  array<string, mixed>  $cfg
     * @return int[]|null
     */
    private function allowedNusqaPartIds(array $cfg, bool $studentChoosesPart): ?array
    {
        if (! $studentChoosesPart || ($cfg['part_type'] ?? null) !== PartType::Nusqa->value || empty($cfg['part_ids'])) {
            return null;
        }

        $ids = Part::where('subject_id', $cfg['subject_id'])
            ->where('type', PartType::Nusqa->value)
            ->whereIn('id', $cfg['part_ids'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $ids ?: null;
    }

    /** @return array<string, mixed> */
    private function formData(?TestAccess $access = null): array
    {
        $mandatorySubjects = Subject::where('is_mandatory', true)->orderBy('title')->get(['id', 'title']);

        $students = User::where('role', Role::Student)
            ->orderBy('name')
            ->get(['id', 'name', 'login'])
            ->mapWithKeys(fn ($u) => [$u->id => ['label' => $u->name, 'subtitle' => $u->login]]);

        $groups = Group::orderBy('title')
            ->get(['id', 'title', 'description'])
            ->mapWithKeys(fn ($g) => [$g->id => ['label' => $g->title, 'subtitle' => $g->description ?? '']]);

        // Subjects with nusqas (+ question counts) and topics
        $subjectsWithParts = Subject::with([
            'parts' => fn ($q) => $q->withCount('questions')->orderBy('title'),
        ])
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn ($s) => [
                $s->id => [
                    'title' => $s->title,
                    'nusqas' => $s->parts
                        ->where('type', PartType::Nusqa->value)
                        ->sortBy('title', SORT_NATURAL)
                        ->map(fn ($p) => ['id' => $p->id, 'title' => $p->title, 'count' => $p->questions_count])
                        ->values()->all(),
                    'topics' => $s->parts
                        ->where('type', PartType::Topic->value)
                        ->map(fn ($p) => ['id' => $p->id, 'title' => $p->title])
                        ->values()->all(),
                ],
            ]);

        $mandatorySubjectsJs = $mandatorySubjects->map(fn ($s) => ['id' => $s->id, 'title' => $s->title])->values();

        $mandatoryIds = $mandatorySubjects->pluck('id')->all();
        $existingElectives = $access
            ? $access->accessSubjects->whereNotIn('subject_id', $mandatoryIds)->values()
            : collect();

        $existingSingle = ($access && $access->type === TestAccessType::Subject)
            ? $access->accessSubjects->first()
            : null;

        $nusqaMode = 'random';
        if ($access) {
            if ($access->student_chooses_nusqa) {
                $nusqaMode = 'student';
            } elseif ($access->nusqa_number !== null) {
                $nusqaMode = 'specific';
            }
        }

        $types = collect(TestAccessType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]);
        $partTypes = collect(PartType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]);

        return compact(
            'mandatorySubjects', 'mandatorySubjectsJs', 'students', 'groups',
            'subjectsWithParts', 'existingElectives', 'existingSingle',
            'nusqaMode', 'types', 'partTypes'
        );
    }
}
