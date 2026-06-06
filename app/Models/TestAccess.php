<?php

namespace App\Models;

use App\Enums\TestAccessType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestAccess extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'group_id',
        'student_chooses_subject',
        'nusqa_number',
        'student_chooses_nusqa',
        'question_count',
        'attempts_limit',
        'expires_at',
    ];

    protected $casts = [
        'type' => TestAccessType::class,
        'student_chooses_subject' => 'boolean',
        'student_chooses_nusqa' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /** Per-subject part configurations (up to 5 for ENT, 1 for Subject). */
    public function accessSubjects(): HasMany
    {
        return $this->hasMany(TestAccessSubject::class)->with(['subject', 'part']);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        $groupId = User::where('id', $userId)->value('group_id');

        return $query
            ->where(function (Builder $q) use ($userId, $groupId) {
                $q->where('user_id', $userId);
                if ($groupId) {
                    $q->orWhere('group_id', $groupId);
                }
            })
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function targetLabel(): string
    {
        return $this->user?->name ?? $this->group?->title ?? '—';
    }
}
