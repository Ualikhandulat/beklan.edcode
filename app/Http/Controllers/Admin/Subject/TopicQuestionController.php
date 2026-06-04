<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Questions\StoreGroupRequest;
use App\Http\Requests\Admin\Questions\StoreMatchRequest;
use App\Http\Requests\Admin\Questions\StoreMultiRequest;
use App\Http\Requests\Admin\Questions\StoreOneRequest;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TopicQuestionController extends Controller
{
    private function back(Subject $subject, Topic $topic): string
    {
        return route('admin.subjects.topics.show', [$subject, $topic]);
    }

    public function index(Subject $subject, Topic $topic): View
    {
        $questions = $topic->questions()
            ->with('detail', 'details')
            ->latest()
            ->get();

        return view('admin.subjects.topics.show', compact('subject', 'topic', 'questions'));
    }

    public function create(Subject $subject, Topic $topic): View
    {
        $type        = request('type');
        $createBase  = route('admin.subjects.topics.questions.create', [$subject, $topic]);
        $backRoute   = route('admin.subjects.topics.show', [$subject, $topic]);
        $storeRoutes = [
            'one'   => route('admin.subjects.topics.questions.store.one',   [$subject, $topic]),
            'multi' => route('admin.subjects.topics.questions.store.multi', [$subject, $topic]),
            'match' => route('admin.subjects.topics.questions.store.match', [$subject, $topic]),
            'group' => route('admin.subjects.topics.questions.store.group', [$subject, $topic]),
        ];
        $contexts = $type === 'group'
            ? $topic->questions()->where('type', QuestionType::IS_GROUP)->get()
            : collect();

        $view = in_array($type, ['one', 'multi', 'match', 'group'])
            ? "admin.subjects.questions.types.{$type}"
            : 'admin.subjects.questions.create';

        return view($view, compact('subject', 'type', 'createBase', 'backRoute', 'storeRoutes', 'contexts'));
    }

    public function storeOne(StoreOneRequest $request, Subject $subject, Topic $topic): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $topic) {
            $q = $topic->questions()->create([
                'subject_id'     => $subject->id,
                'type'           => QuestionType::SELECT_ONE,
                'count_variants' => collect([$data['var1'], $data['var2'], $data['var3'], $data['var4'] ?? null, $data['var5'] ?? null])->filter()->count(),
                'count_answers'  => 1,
                'text'           => '',
            ]);
            $q->detail()->create([
                'question' => $data['question'],
                'answers'  => [1],
                'var1'     => $data['var1'],
                'var2'     => $data['var2'],
                'var3'     => $data['var3'],
                'var4'     => $data['var4'] ?? null,
                'var5'     => $data['var5'] ?? null,
            ]);
        });

        return redirect($this->back($subject, $topic))->with('success', 'Вопрос добавлен.');
    }

    public function storeMulti(StoreMultiRequest $request, Subject $subject, Topic $topic): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $topic) {
            $q = $topic->questions()->create([
                'subject_id'     => $subject->id,
                'type'           => QuestionType::SELECT_MULTI,
                'count_variants' => 6,
                'count_answers'  => count($data['answers']),
                'text'           => '',
            ]);
            $detail = ['question' => $data['question'], 'answers' => array_map('intval', $data['answers'])];
            for ($i = 1; $i <= 8; $i++) {
                $detail["var{$i}"] = $data["var{$i}"] ?? null;
            }
            $q->detail()->create($detail);
        });

        return redirect($this->back($subject, $topic))->with('success', 'Вопрос добавлен.');
    }

    public function storeMatch(StoreMatchRequest $request, Subject $subject, Topic $topic): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $topic) {
            $q = $topic->questions()->create([
                'subject_id'     => $subject->id,
                'type'           => QuestionType::IS_MATCH,
                'count_variants' => 2,
                'count_answers'  => 2,
                'text'           => '',
            ]);
            $q->detail()->create([
                'question' => $data['question'] ?? null,
                'answers'  => [5, 6],
                'var1'     => $data['var1'],
                'var2'     => $data['var2'],
                'var5'     => $data['var5'],
                'var6'     => $data['var6'],
                'var7'     => $data['var7'],
                'var8'     => $data['var8'] ?? null,
            ]);
        });

        return redirect($this->back($subject, $topic))->with('success', 'Вопрос добавлен.');
    }

    public function storeGroup(StoreGroupRequest $request, Subject $subject, Topic $topic): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $topic) {
            if ($data['context_mode'] === 'new') {
                // Создаём IS_GROUP вопрос — контекст
                $context = $topic->questions()->create([
                    'subject_id'     => $subject->id,
                    'type'           => QuestionType::IS_GROUP,
                    'count_variants' => 0,
                    'count_answers'  => 0,
                    'text'           => $data['context_text'],
                ]);
            } else {
                $context = Question::findOrFail($data['context_id']);
            }

            // Подвопрос — новая строка в question_details того же IS_GROUP вопроса
            $context->details()->create([
                'question' => $data['question'],
                'answers'  => [1],
                'var1'     => $data['var1'],
                'var2'     => $data['var2'],
                'var3'     => $data['var3'],
                'var4'     => $data['var4'] ?? null,
                'var5'     => $data['var5'] ?? null,
            ]);
        });

        return redirect($this->back($subject, $topic))->with('success', 'Подвопрос добавлен.');
    }

    public function destroy(Subject $subject, Topic $topic, Question $question): RedirectResponse
    {
        $question->delete();

        return redirect($this->back($subject, $topic))->with('success', 'Вопрос удалён.');
    }
}
