<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Questions\StoreGroupRequest;
use App\Http\Requests\Admin\Questions\StoreMatchRequest;
use App\Http\Requests\Admin\Questions\StoreMultiRequest;
use App\Http\Requests\Admin\Questions\StoreOneRequest;
use App\Models\Part;
use App\Models\Question;
use App\Models\QuestionDetail;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartQuestionController extends Controller
{
    private function back(Subject $subject, Part $part): string
    {
        return route('admin.subjects.parts.show', [$subject, $part]);
    }

    public function storeOne(StoreOneRequest $request, Subject $subject, Part $part): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $part) {
            $vars = [$data['var1'], $data['var2'], $data['var3'], $data['var4'] ?? null, $data['var5'] ?? null];
            $countVariants = collect($vars)->filter()->count();

            $q = $part->questions()->create([
                'subject_id' => $subject->id,
                'type' => QuestionType::SELECT_ONE,
                'count_variants' => $countVariants,
                'count_answers' => 1,
            ]);
            $q->detail()->create([
                'question' => $data['question'],
                'answers' => [1],
                'var1' => $data['var1'],
                'var2' => $data['var2'],
                'var3' => $data['var3'],
                'var4' => $data['var4'] ?? null,
                'var5' => $data['var5'] ?? null,
            ]);
        });

        return redirect($this->back($subject, $part))->with('success', 'Вопрос добавлен.');
    }

    public function storeMulti(StoreMultiRequest $request, Subject $subject, Part $part): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $part) {
            $countVariants = collect(range(1, 8))
                ->filter(fn ($i) => ! empty($data["var{$i}"]))
                ->count();

            $q = $part->questions()->create([
                'subject_id' => $subject->id,
                'type' => QuestionType::SELECT_MULTI,
                'count_variants' => $countVariants,
                'count_answers' => count($data['answers']),
            ]);
            $detail = ['question' => $data['question'], 'answers' => array_map('intval', $data['answers'])];
            for ($i = 1; $i <= 8; $i++) {
                $detail["var{$i}"] = $data["var{$i}"] ?? null;
            }
            $q->detail()->create($detail);
        });

        return redirect($this->back($subject, $part))->with('success', 'Вопрос добавлен.');
    }

    public function storeMatch(StoreMatchRequest $request, Subject $subject, Part $part): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $part) {
            $q = $part->questions()->create([
                'subject_id' => $subject->id,
                'type' => QuestionType::IS_MATCH,
                'count_variants' => 2,
                'count_answers' => 2,
            ]);
            $q->detail()->create([
                'question' => $data['question'],
                'answers' => [3, 4],
                'var1' => $data['var1'],
                'var2' => $data['var2'],
                'var5' => $data['var5'],
                'var6' => $data['var6'],
                'var7' => $data['var7'],
                'var8' => $data['var8'],
            ]);
        });

        return redirect($this->back($subject, $part))->with('success', 'Вопрос добавлен.');
    }

    public function storeGroup(StoreGroupRequest $request, Subject $subject, Part $part): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $subject, $part) {
            if ($data['context_mode'] === 'new') {
                $context = $part->questions()->create([
                    'subject_id' => $subject->id,
                    'type' => QuestionType::IS_GROUP,
                    'count_variants' => 0,
                    'count_answers' => 0,
                    'text' => $data['context_text'],
                ]);
            } else {
                $context = Question::findOrFail($data['context_id']);
            }

            $context->details()->create([
                'question' => $data['question'],
                'answers' => [1],
                'var1' => $data['var1'],
                'var2' => $data['var2'],
                'var3' => $data['var3'],
                'var4' => $data['var4'] ?? null,
                'var5' => $data['var5'] ?? null,
            ]);
        });

        return redirect($this->back($subject, $part))->with('success', 'Вопрос добавлен.');
    }

    public function edit(Subject $subject, Part $part, Question $question): View
    {
        $question->load('detail', 'details');
        $contexts = $question->type === QuestionType::IS_GROUP
            ? collect()
            : $part->questions()->where('type', QuestionType::IS_GROUP)->get();

        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            route('admin.subjects.show', $subject) => $subject->title,
            route('admin.subjects.parts.show', [$subject, $part]) => $part->title,
            '#' => 'Изменить вопрос',
        ];

        return view('admin.subjects.parts.edit-question', [
            'subject' => $subject,
            'part' => $part,
            'question' => $question,
            'contexts' => $contexts,
            'updateRoute' => route('admin.subjects.parts.questions.update', [$subject, $part, $question]),
            'showUrl' => $this->back($subject, $part),
            'navigations' => $navigations,
        ]);
    }

    public function update(Request $request, Subject $subject, Part $part, Question $question): RedirectResponse
    {
        DB::transaction(function () use ($request, $question) {
            match ($question->type) {
                QuestionType::SELECT_ONE => $this->applyOne($request, $question),
                QuestionType::SELECT_MULTI => $this->applyMulti($request, $question),
                QuestionType::IS_MATCH => $this->applyMatch($request, $question),
                QuestionType::IS_GROUP => $this->applyGroup($request, $question),
            };
        });

        return redirect($this->back($subject, $part))->with('success', 'Вопрос обновлён.');
    }

    private function applyOne(Request $request, Question $question): void
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'var1' => ['required', 'string'],
            'var2' => ['required', 'string'],
            'var3' => ['required', 'string'],
            'var4' => ['nullable', 'string'],
            'var5' => ['nullable', 'string'],
        ]);

        $vars = [$data['var1'], $data['var2'], $data['var3'], $data['var4'] ?? null, $data['var5'] ?? null];
        $question->update(['count_variants' => collect($vars)->filter()->count()]);
        $question->detail()->updateOrCreate([], [
            'question' => $data['question'],
            'answers' => [1],
            'var1' => $data['var1'], 'var2' => $data['var2'], 'var3' => $data['var3'],
            'var4' => $data['var4'] ?? null, 'var5' => $data['var5'] ?? null,
        ]);
    }

    private function applyMulti(Request $request, Question $question): void
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'answers' => ['required', 'array', 'min:1', 'max:3'],
            'answers.*' => ['integer', 'min:1', 'max:8'],
            'var1' => ['required', 'string'], 'var2' => ['required', 'string'],
            'var3' => ['required', 'string'], 'var4' => ['required', 'string'],
            'var5' => ['required', 'string'], 'var6' => ['required', 'string'],
            'var7' => ['nullable', 'string'], 'var8' => ['nullable', 'string'],
        ]);

        $countVariants = collect(range(1, 8))->filter(fn ($i) => ! empty($data["var{$i}"]))->count();
        $question->update(['count_variants' => $countVariants, 'count_answers' => count($data['answers'])]);

        $detail = ['question' => $data['question'], 'answers' => array_map('intval', $data['answers'])];
        for ($i = 1; $i <= 8; $i++) {
            $detail["var{$i}"] = $data["var{$i}"] ?? null;
        }
        $question->detail()->updateOrCreate([], $detail);
    }

    private function applyMatch(Request $request, Question $question): void
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'var1' => ['required', 'string'], 'var2' => ['required', 'string'],
            'var5' => ['required', 'string'], 'var6' => ['required', 'string'],
            'var7' => ['required', 'string'], 'var8' => ['required', 'string'],
        ]);

        $question->detail()->updateOrCreate([], [
            'question' => $data['question'],
            'answers' => [3, 4],
            'var1' => $data['var1'], 'var2' => $data['var2'],
            'var5' => $data['var5'], 'var6' => $data['var6'],
            'var7' => $data['var7'], 'var8' => $data['var8'],
        ]);
    }

    private function applyGroup(Request $request, Question $question): void
    {
        $data = $request->validate(['context_text' => ['required', 'string']]);
        $question->update(['text' => $data['context_text']]);
    }

    public function destroy(Subject $subject, Part $part, Question $question): RedirectResponse
    {
        $question->delete();

        return redirect($this->back($subject, $part))->with('success', 'Вопрос удалён.');
    }

    public function editDetail(Subject $subject, Part $part, Question $question, QuestionDetail $detail): View
    {
        $navigations = [
            route('admin.subjects.index') => 'Предметы',
            route('admin.subjects.show', $subject) => $subject->title,
            route('admin.subjects.parts.show', [$subject, $part]) => $part->title,
            '#' => 'Изменить вопрос',
        ];

        return view('admin.subjects.parts.edit-detail', [
            'subject' => $subject,
            'part' => $part,
            'question' => $question,
            'detail' => $detail,
            'updateRoute' => route('admin.subjects.parts.questions.details.update', [$subject, $part, $question, $detail]),
            'showUrl' => $this->back($subject, $part),
            'navigations' => $navigations,
        ]);
    }

    public function updateDetail(Request $request, Subject $subject, Part $part, Question $question, QuestionDetail $detail): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'var1' => ['required', 'string'],
            'var2' => ['required', 'string'],
            'var3' => ['required', 'string'],
            'var4' => ['nullable', 'string'],
            'var5' => ['nullable', 'string'],
        ]);

        $detail->update([
            'question' => $data['question'],
            'answers' => [1],
            'var1' => $data['var1'],
            'var2' => $data['var2'],
            'var3' => $data['var3'],
            'var4' => $data['var4'] ?? null,
            'var5' => $data['var5'] ?? null,
        ]);

        return redirect($this->back($subject, $part))->with('success', 'Вопрос обновлён.');
    }

    public function destroyDetail(Subject $subject, Part $part, Question $question, QuestionDetail $detail): RedirectResponse
    {
        $detail->delete();

        return redirect($this->back($subject, $part))->with('success', 'Вопрос удалён.');
    }
}
