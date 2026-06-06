<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'part_id',
        'type',
        'count_variants',
        'count_answers',
        'text',
    ];

    protected $casts = [
        'type' => QuestionType::class,
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(QuestionDetail::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(QuestionDetail::class);
    }
}
