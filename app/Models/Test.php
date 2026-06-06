<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $fillable = [
        'test_access_id',
        'user_id',
        'attempt_number',
        'started_at',
        'completed_at',
        'total_score',
        'max_score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function access(): BelongsTo
    {
        return $this->belongsTo(TestAccess::class, 'test_access_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(TestSubject::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isExpired(): bool
    {
        $duration = $this->relationLoaded('access') ? $this->access?->duration_minutes : null;

        if (! $duration || ! $this->started_at) {
            return false;
        }

        return $this->started_at->addMinutes($duration)->isPast();
    }

    /** Seconds remaining before time runs out (null = no limit). */
    public function secondsRemaining(): ?int
    {
        $duration = $this->relationLoaded('access') ? $this->access?->duration_minutes : null;

        if (! $duration || ! $this->started_at) {
            return null;
        }

        return max(0, (int) now()->diffInSeconds($this->started_at->addMinutes($duration), false));
    }
}
