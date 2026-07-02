<?php

namespace App\Exports;

use App\Exports\Sheets\ResultsSheet;
use App\Exports\Sheets\SubjectSummarySheet;
use App\Models\Test;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ResultsExport implements WithMultipleSheets
{
    /** @param Collection<int, Test> $tests Tests with user.group, access and subjects.subject loaded. */
    public function __construct(private Collection $tests) {}

    /** @return array<int, object> */
    public function sheets(): array
    {
        $subjects = $this->tests
            ->pluck('subjects')
            ->flatten()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('title')
            ->values();

        return [
            new ResultsSheet($this->tests, $subjects),
            new SubjectSummarySheet($this->tests, $subjects),
        ];
    }
}
