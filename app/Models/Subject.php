<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'is_ent_subject',
        'is_active',
    ];

    protected $casts = [
        'is_ent_subject'    => 'boolean',
        'is_active'         => 'boolean',
    ];
}
