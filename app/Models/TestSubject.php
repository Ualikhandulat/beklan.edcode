<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestSubject extends Model
{
    protected $fillable = [
        'test_id',
        'subject_id',
        'part_id',
        'questions',
        'score',
        'max_score',
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /** Total questions answered (user_answers not empty). */
    public function answeredCount(): int
    {
        return collect($this->questions)
            ->filter(fn ($q) => ! empty($q['user_answers']))
            ->count();
    }
}
