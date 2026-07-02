<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\FormatsResultSheet;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class ResultsSheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    use FormatsResultSheet;

    /**
     * @param  Collection<int, Test>  $tests
     * @param  Collection<int, Subject>  $subjects  Все предметы, встречающиеся в выборке (колонки листа).
     */
    public function __construct(
        private Collection $tests,
        private Collection $subjects,
    ) {}

    public function title(): string
    {
        return 'Результаты';
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            '№',
            'Студент',
            'Логин',
            'ИИН',
            'Группа',
            'Тип теста',
            'Попытка',
            ...$this->subjects->map(fn (Subject $subject) => $subject->title),
            'Балл',
            'Макс. балл',
            'Процент',
            'Начат',
            'Завершён',
            'Длительность',
            'Статус',
        ];
    }

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        return $this->tests->values()->map(function (Test $test, int $index) {
            $scoresBySubject = $test->subjects->keyBy('subject_id');

            $subjectScores = $this->subjects->map(function (Subject $subject) use ($test, $scoresBySubject) {
                $testSubject = $scoresBySubject->get($subject->id);

                return ($testSubject && $test->isCompleted()) ? (int) $testSubject->score : null;
            });

            return [
                $index + 1,
                $test->user?->name ?? '—',
                $test->user?->login,
                $test->user?->iin,
                $test->user?->group?->title,
                $test->access?->type->label(),
                $test->attempt_number,
                ...$subjectScores,
                $test->isCompleted() ? (int) $test->total_score : null,
                $test->isCompleted() ? (int) $test->max_score : null,
                $test->percent(),
                $test->started_at?->format('d.m.Y H:i'),
                $test->completed_at?->format('d.m.Y H:i'),
                $test->durationLabel(),
                $this->statusLabel($test),
            ];
        })->all();
    }

    private function statusLabel(Test $test): string
    {
        return match (true) {
            $test->isCompleted() => 'Завершён',
            $test->isExpired() => 'Истёк',
            default => 'В процессе',
        };
    }
}
