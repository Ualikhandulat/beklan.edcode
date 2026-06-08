<?php

namespace App\Models;

use App\Enums\PartType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'is_ent_subject',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'is_ent_subject' => 'boolean',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Part::class)->where('type', PartType::Topic);
    }

    public function nusqas(): HasMany
    {
        return $this->hasMany(Part::class)->where('type', PartType::Nusqa);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
