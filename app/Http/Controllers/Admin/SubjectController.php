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

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subjects.form', [
            'subject' => new Subject(),
            'action'  => route('admin.subjects.store'),
            'method'  => 'POST',
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

        return view('admin.subjects.show', compact('subject', 'tab'));
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.form', [
            'subject' => $subject,
            'action'  => route('admin.subjects.update', $subject),
            'method'  => 'PUT',
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
