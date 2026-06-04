<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject_id',
        'topic_id',
        'count_variants',
        'count_answers',
        'text',
    ];

    public function questionable(): MorphTo
    {
        return $this->morphTo();  // может быть Topic или Variant
    }
}
