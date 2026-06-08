<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'question',
        'answers',
        'var1', 'var2', 'var3', 'var4', 'var5',
        'var6', 'var7', 'var8', 'var9', 'var10',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
