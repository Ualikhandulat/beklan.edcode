<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\TrialAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function __construct(private TrialAccessService $trialAccess) {}

    public function index(): View
    {
        $subjects = Subject::withCount(['topics', 'nusqas', 'questions', 'parts'])->latest()->get();

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
        ];

        return view('admin.subjects.index', compact('subjects', 'navigations'));
    }

    public function create(): View
    {
        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            '#' => 'Добавить предмет',
        ];

        return view('admin.subjects.form', [
            'subject' => new Subject,
            'action' => route('admin.subjects.store'),
            'method' => 'POST',
            'navigations' => $navigations,
        ]);
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Предмет создан.');
    }

    public function show(Subject $subject): View
    {
        $tab = request('tab', 'topics');
        $subject->load(['parts.questions']);

        $topics = $subject->parts->where('type', PartType::Topic);
        $nusqas = $subject->parts->where('type', PartType::Nusqa)->sortBy('title', SORT_NATURAL)->values();

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            '#' => $subject->title,
        ];

        $trialPartId = $this->trialAccess->trialPartId();

        return view('admin.subjects.show', compact('subject', 'tab', 'topics', 'nusqas', 'navigations', 'trialPartId'));
    }

    public function edit(Subject $subject): View|RedirectResponse
    {
        if ($subject->is_mandatory) {
            return redirect()->route('admin.subjects.show', $subject)
                ->with('error', 'Обязательный предмет ЕНТ нельзя редактировать.');
        }

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            route('admin.subjects.show', $subject) => $subject->title,
            '#' => 'Редактировать',
        ];

        return view('admin.subjects.form', [
            'subject' => $subject,
            'action' => route('admin.subjects.update', $subject),
            'method' => 'PUT',
            'navigations' => $navigations,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        if ($subject->is_mandatory) {
            return redirect()->route('admin.subjects.show', $subject)
                ->with('error', 'Обязательный предмет ЕНТ нельзя редактировать.');
        }

        $subject->update($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Предмет обновлён.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->is_mandatory) {
            return redirect()->back()
                ->with('error', 'Обязательный предмет ЕНТ нельзя удалить.');
        }

        if ($subject->parts()->exists()) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить «'.$subject->title.'»: сначала удалите все темы и нұсқалар.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Предмет удалён.');
    }
}
