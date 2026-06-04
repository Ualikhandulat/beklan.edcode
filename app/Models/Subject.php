<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'title',
        'image',
        'is_ent_subject',
        'is_active',
    ];

    protected $casts = [
        'is_ent_subject' => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function nusqas(): HasMany
    {
        return $this->hasMany(Nusqa::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
