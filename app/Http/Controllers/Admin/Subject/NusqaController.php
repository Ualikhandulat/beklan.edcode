<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNusqaRequest;
use App\Models\Nusqa;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NusqaController extends Controller
{
    public function create(Subject $subject): View
    {
        return view('admin.subjects.nusqas.form', [
            'subject' => $subject,
            'nusqa'   => new Nusqa(),
            'action'  => route('admin.subjects.nusqas.store', $subject),
            'method'  => 'POST',
        ]);
    }

    public function store(StoreNusqaRequest $request, Subject $subject): RedirectResponse
    {
        $subject->nusqas()->create($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Нұсқа добавлена.');
    }

    public function show(Subject $subject, Nusqa $nusqa): View
    {
        $type      = request('type');
        $questions = $nusqa->questions()->with('detail', 'details')->latest()->get();

        $showUrl = route('admin.subjects.nusqas.show', [$subject, $nusqa]);

        $storeRoutes = [
            'one'   => route('admin.subjects.nusqas.questions.store.one',   [$subject, $nusqa]),
            'multi' => route('admin.subjects.nusqas.questions.store.multi', [$subject, $nusqa]),
            'match' => route('admin.subjects.nusqas.questions.store.match', [$subject, $nusqa]),
            'group' => route('admin.subjects.nusqas.questions.store.group', [$subject, $nusqa]),
        ];

        $contexts = $type === 'group'
            ? $nusqa->questions()->where('type', QuestionType::IS_GROUP)->get()
            : collect();

        return view('admin.subjects.nusqas.show',
            compact('subject', 'nusqa', 'questions', 'type', 'showUrl', 'storeRoutes', 'contexts'));
    }

    public function edit(Subject $subject, Nusqa $nusqa): View
    {
        return view('admin.subjects.nusqas.form', [
            'subject' => $subject,
            'nusqa'   => $nusqa,
            'action'  => route('admin.subjects.nusqas.update', [$subject, $nusqa]),
            'method'  => 'PUT',
        ]);
    }

    public function update(StoreNusqaRequest $request, Subject $subject, Nusqa $nusqa): RedirectResponse
    {
        $nusqa->update($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Нұсқа обновлена.');
    }

    public function destroy(Subject $subject, Nusqa $nusqa): RedirectResponse
    {
        $nusqa->delete();

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Нұсқа удалена.');
    }
}
