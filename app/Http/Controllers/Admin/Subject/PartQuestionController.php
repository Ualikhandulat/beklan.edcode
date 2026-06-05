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
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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
                'answers' => [5, 6],
                'var1' => $data['var1'],
                'var2' => $data['var2'],
                'var5' => $data['var5'],
                'var6' => $data['var6'],
                'var7' => $data['var7'],
                'var8' => $data['var8'] ?? null,
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

        return redirect($this->back($subject, $part))->with('success', 'Подвопрос добавлен.');
    }

    public function destroy(Subject $subject, Part $part, Question $question): RedirectResponse
    {
        $question->delete();

        return redirect($this->back($subject, $part))->with('success', 'Вопрос удалён.');
    }
}
