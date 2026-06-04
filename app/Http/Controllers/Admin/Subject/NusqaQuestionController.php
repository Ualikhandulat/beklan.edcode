<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Questions\StoreGroupRequest;
use App\Http\Requests\Admin\Questions\StoreMatchRequest;
use App\Http\Requests\Admin\Questions\StoreMultiRequest;
use App\Http\Requests\Admin\Questions\StoreOneRequest;
use App\Models\Nusqa;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NusqaQuestionController extends Controller
{
    private function back(Subject $subject, Nusqa $nusqa): string
    {
        return route('admin.subjects.nusqas.show', [$subject, $nusqa]);
    }

    public function index(Subject $subject, Nusqa $nusqa): View
    {
        $questions = $nusqa->questions()
            ->with('detail', 'details')
            ->latest()
            ->get();

        return view('admin.subjects.nusqas.show', compact('subject', 'nusqa', 'questions'));
    }

    public function create(Subject $subject, Nusqa $nusqa): View
    {
        $type        = request('type');
        $createBase  = route('admin.subjects.nusqas.questions.create', [$subject, $nusqa]);
        $backRoute   = route('admin.subjects.nusqas.show', [$subject, $nusqa]);
        $storeRoutes = [
            'one'   => route('admin.subjects.nusqas.questions.store.one',   [$subject, $nusqa]),
            'multi' => route('admin.subjects.nusqas.questions.store.multi', [$subject, $nusqa]),
            'match' => route('admin.subjects.nusqas.questions.store.match', [$subject, $nusqa]),
            'group' => route('admin.subjects.nusqas.questions.store.group', [$subject, $nusqa]),
        ];
        $contexts = $type === 'group'
            ? $nusqa->questions()->where('type', QuestionType::IS_GROUP)->get()
            : collect();

        $view = in_array($type, ['one', 'multi', 'match', 'group'])
            ? "admin.subjects.questions.types.{$type}"
            : 'admin.subjects.questions.create';

        return view($view, compact('subject', 'type', 'createBase', 'backRoute', 'storeRoutes', 'contexts'));
    }

    public function storeOne(StoreOneRequest $request, Subject $subject, Nusqa $nusqa): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $nusqa) {
            $q = $nusqa->questions()->create([
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

        return redirect($this->back($subject, $nusqa))->with('success', 'Вопрос добавлен.');
    }

    public function storeMulti(StoreMultiRequest $request, Subject $subject, Nusqa $nusqa): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $nusqa) {
            $q = $nusqa->questions()->create([
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

        return redirect($this->back($subject, $nusqa))->with('success', 'Вопрос добавлен.');
    }

    public function storeMatch(StoreMatchRequest $request, Subject $subject, Nusqa $nusqa): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $nusqa) {
            $q = $nusqa->questions()->create([
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

        return redirect($this->back($subject, $nusqa))->with('success', 'Вопрос добавлен.');
    }

    public function storeGroup(StoreGroupRequest $request, Subject $subject, Nusqa $nusqa): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $nusqa) {
            if ($data['context_mode'] === 'new') {
                $context = $nusqa->questions()->create([
                    'subject_id'     => $subject->id,
                    'type'           => QuestionType::IS_GROUP,
                    'count_variants' => 0,
                    'count_answers'  => 0,
                    'text'           => $data['context_text'],
                ]);
            } else {
                $context = Question::findOrFail($data['context_id']);
            }

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

        return redirect($this->back($subject, $nusqa))->with('success', 'Подвопрос добавлен.');
    }

    public function destroy(Subject $subject, Nusqa $nusqa, Question $question): RedirectResponse
    {
        $question->delete();

        return redirect($this->back($subject, $nusqa))->with('success', 'Вопрос удалён.');
    }
}
