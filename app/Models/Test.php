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

    /** Score as a percentage of the max (null when not completed or max is 0). */
    public function percent(): ?int
    {
        if (! $this->isCompleted() || ! $this->max_score) {
            return null;
        }

        return (int) round($this->total_score / $this->max_score * 100);
    }

    /** Human-readable duration between start and finish, e.g. "1ч 5м" (null when not completed). */
    public function durationLabel(): ?string
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        $seconds = (int) $this->started_at->diffInSeconds($this->completed_at);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0 ? "{$h}ч {$m}м" : ($m > 0 ? "{$m}м {$s}с" : "{$s}с");
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
