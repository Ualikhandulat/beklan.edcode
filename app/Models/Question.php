<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Question extends Model
{
    protected $fillable = [
        'subject_id',
        'type',
        'questionable_id',
        'questionable_type',
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

    public function questionable(): MorphTo
    {
        return $this->morphTo();
    }

    // Для SELECT_ONE / SELECT_MULTI / IS_MATCH — одна деталь
    public function detail(): HasOne
    {
        return $this->hasOne(QuestionDetail::class);
    }

    // Для IS_GROUP — несколько деталей (подвопросов)
    public function details(): HasMany
    {
        return $this->hasMany(QuestionDetail::class);
    }
}
