<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTopicRequest;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TopicController extends Controller
{
    public function create(Subject $subject): View
    {
        return view('admin.subjects.topics.form', [
            'subject' => $subject,
            'topic'   => new Topic(),
            'action'  => route('admin.subjects.topics.store', $subject),
            'method'  => 'POST',
        ]);
    }

    public function store(StoreTopicRequest $request, Subject $subject): RedirectResponse
    {
        $subject->topics()->create($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Тема добавлена.');
    }

    public function show(Subject $subject, Topic $topic): View
    {
        $type      = request('type');
        $questions = $topic->questions()->with('detail', 'details')->latest()->get();

        $showUrl = route('admin.subjects.topics.show', [$subject, $topic]);

        $storeRoutes = [
            'one'   => route('admin.subjects.topics.questions.store.one',   [$subject, $topic]),
            'multi' => route('admin.subjects.topics.questions.store.multi', [$subject, $topic]),
            'match' => route('admin.subjects.topics.questions.store.match', [$subject, $topic]),
            'group' => route('admin.subjects.topics.questions.store.group', [$subject, $topic]),
        ];

        $contexts = $type === 'group'
            ? $topic->questions()->where('type', QuestionType::IS_GROUP)->get()
            : collect();

        return view('admin.subjects.topics.show',
            compact('subject', 'topic', 'questions', 'type', 'showUrl', 'storeRoutes', 'contexts'));
    }

    public function edit(Subject $subject, Topic $topic): View
    {
        return view('admin.subjects.topics.form', [
            'subject' => $subject,
            'topic'   => $topic,
            'action'  => route('admin.subjects.topics.update', [$subject, $topic]),
            'method'  => 'PUT',
        ]);
    }

    public function update(StoreTopicRequest $request, Subject $subject, Topic $topic): RedirectResponse
    {
        $topic->update($request->validated());

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Тема обновлена.');
    }

    public function destroy(Subject $subject, Topic $topic): RedirectResponse
    {
        $topic->delete();

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Тема удалена.');
    }
}
