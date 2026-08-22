<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\PartType;
use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePartRequest;
use App\Models\Part;
use App\Models\Subject;
use App\Services\TrialAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartController extends Controller
{
    public function __construct(private TrialAccessService $trialAccess) {}

    public function create(Subject $subject): View
    {
        $type = PartType::tryFrom(request('type', '')) ?? PartType::Topic;

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            route('admin.subjects.show', $subject) => $subject->title,
            '#' => $type->createLabel(),
        ];

        return view('admin.subjects.parts.form', [
            'subject' => $subject,
            'part' => new Part(['type' => $type]),
            'action' => route('admin.subjects.parts.store', $subject),
            'method' => 'POST',
            'navigations' => $navigations,
            'isTrialPart' => false,
        ]);
    }

    public function store(StorePartRequest $request, Subject $subject): RedirectResponse
    {
        $part = $subject->parts()->create($request->safe()->except('is_trial'));

        $this->syncTrialFlag($request, $part);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Добавлено.');
    }

    public function show(Subject $subject, Part $part): View
    {
        $type = request('type');
        $questions = $part->questions()->with('detail', 'details')->latest('id')->get();
        $showUrl = route('admin.subjects.parts.show', [$subject, $part]);
        $storeRoutes = [
            'one' => route('admin.subjects.parts.questions.store.one', [$subject, $part]),
            'multi' => route('admin.subjects.parts.questions.store.multi', [$subject, $part]),
            'match' => route('admin.subjects.parts.questions.store.match', [$subject, $part]),
            'group' => route('admin.subjects.parts.questions.store.group', [$subject, $part]),
        ];
        $contexts = $type === 'group'
            ? $part->questions()->where('type', QuestionType::IS_GROUP)->get()
            : collect();

        $navigations = $type !== null
            ? [
                route('admin.subjects.index') => 'Предметы',
                route('admin.subjects.show', $subject) => $subject->title,
                route('admin.subjects.parts.show', [$subject, $part]) => $part->title,
                '#' => 'Добавить вопрос',
            ]
            : [
                route('admin.subjects.index') => 'Предметы',
                route('admin.subjects.show', $subject) => $subject->title,
                '#' => $part->title,
            ];

        return view('admin.subjects.parts.show',
            compact('subject', 'part', 'questions', 'type', 'showUrl', 'storeRoutes', 'contexts', 'navigations'));
    }

    public function edit(Subject $subject, Part $part): View
    {
        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            route('admin.subjects.show', $subject) => $subject->title,
            '#' => $part->title,
        ];

        return view('admin.subjects.parts.form', [
            'subject' => $subject,
            'part' => $part,
            'action' => route('admin.subjects.parts.update', [$subject, $part]),
            'method' => 'PUT',
            'navigations' => $navigations,
            'isTrialPart' => $this->trialAccess->trialPartId() === $part->id,
        ]);
    }

    public function update(StorePartRequest $request, Subject $subject, Part $part): RedirectResponse
    {
        $part->update($request->safe()->except('is_trial'));

        $this->syncTrialFlag($request, $part);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Обновлено.');
    }

    /** Галочка «Пробный нұсқа»: назначает или снимает пробный доступ для нұсқа. */
    private function syncTrialFlag(StorePartRequest $request, Part $part): void
    {
        if ($part->type !== PartType::Nusqa) {
            return;
        }

        if ($request->boolean('is_trial')) {
            $this->trialAccess->assignPart($part);
        } elseif ($this->trialAccess->trialPartId() === $part->id) {
            $this->trialAccess->unassignPart($part);
        }
    }

    public function destroy(Subject $subject, Part $part): RedirectResponse
    {
        if ($part->questions()->exists()) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить «'.$part->title.'»: сначала удалите все вопросы.');
        }

        $part->delete();

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Удалено.');
    }
}
