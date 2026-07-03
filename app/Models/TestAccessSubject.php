<?php

namespace App\Models;

use App\Enums\PartType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAccessSubject extends Model
{
    protected $fillable = [
        'test_access_id',
        'subject_id',
        'part_type',
        'part_id',
        'part_ids',
        'student_chooses_part',
    ];

    protected $casts = [
        'part_type' => PartType::class,
        'part_ids' => 'array',
        'student_chooses_part' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
