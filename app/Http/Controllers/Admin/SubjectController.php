<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::withCount(['topics', 'nusqas', 'questions'])->latest()->get();

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
        ];

        return view('admin.subjects.index', compact('subjects', 'navigations'));
    }

    public function create(): View
    {
        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            '#'                           => 'Добавить предмет',
        ];

        return view('admin.subjects.form', [
            'subject'     => new Subject(),
            'action'      => route('admin.subjects.store'),
            'method'      => 'POST',
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
        $subject->load(['topics.questions', 'nusqas.questions']);

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            '#'                           => $subject->title,
        ];

        return view('admin.subjects.show', compact('subject', 'tab', 'navigations'));
    }

    public function edit(Subject $subject): View
    {
        $navigations = [
            route('admin.subjects.index')        => 'Предметы',
            route('admin.subjects.show', $subject) => $subject->title,
            '#'                                  => 'Редактировать',
        ];

        return view('admin.subjects.form', [
            'subject'     => $subject,
            'action'      => route('admin.subjects.update', $subject),
            'method'      => 'PUT',
            'navigations' => $navigations,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Предмет обновлён.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Предмет удалён.');
    }
}
