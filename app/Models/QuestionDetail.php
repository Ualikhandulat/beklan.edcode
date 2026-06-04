<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'var1',
        'var2',
        'var3',
        'var4',
        'var5',
        'var6',
        'var7',
        'var8',
        'var9',
        'var10',

        'answers',
    ];

    protected $hidden = [
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];
}
