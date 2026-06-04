<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Topic extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'questionable');
    }
}
