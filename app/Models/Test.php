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
        'expires_at',
        'completed_at',
        'total_score',
        'max_score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
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
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Seconds remaining before time runs out (null = no limit). */
    public function secondsRemaining(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }
}
