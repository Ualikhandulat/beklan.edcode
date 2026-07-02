<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\FormatsResultSheet;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestSubject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class SubjectSummarySheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    use FormatsResultSheet;

    /**
     * @param  Collection<int, Test>  $tests
     * @param  Collection<int, Subject>  $subjects
     */
    public function __construct(
        private Collection $tests,
        private Collection $subjects,
    ) {}

    public function title(): string
    {
        return 'Сводка по предметам';
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'Предмет',
            'Завершённых попыток',
            'Средний балл',
            'Средний макс. балл',
            'Средний %',
            'Лучший %',
            'Худший %',
        ];
    }

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        $completedTests = $this->tests->filter(fn (Test $test) => $test->isCompleted());

        return $this->subjects->map(function (Subject $subject) use ($completedTests) {
            $entries = $completedTests->flatMap(
                fn (Test $test) => $test->subjects->where('subject_id', $subject->id)
            );

            $percents = $entries
                ->filter(fn (TestSubject $ts) => $ts->max_score > 0)
                ->map(fn (TestSubject $ts) => $ts->score / $ts->max_score * 100);

            return [
                $subject->title,
                $entries->count(),
                $entries->isNotEmpty() ? round($entries->avg('score'), 1) : null,
                $entries->isNotEmpty() ? round($entries->avg('max_score'), 1) : null,
                $percents->isNotEmpty() ? (int) round($percents->avg()) : null,
                $percents->isNotEmpty() ? (int) round($percents->max()) : null,
                $percents->isNotEmpty() ? (int) round($percents->min()) : null,
            ];
        })->all();
    }
}
