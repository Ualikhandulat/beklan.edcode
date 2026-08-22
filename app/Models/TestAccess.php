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
        'is_active',
        'is_trial',
        'student_chooses_subject',
        'nusqa_number',
        'student_chooses_nusqa',
        'question_count',
        'attempts_limit',
        'expires_at',
        'duration_minutes',
    ];

    protected $casts = [
        'type' => TestAccessType::class,
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
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

    public function tests(): HasMany
    {
        return $this->hasMany(Test::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();
        $userId = $user->id;
        $groupId = $user->group_id;

        return $query
            ->where('is_active', true)
            ->where(function (Builder $q) use ($user, $userId, $groupId) {
                $q->where('user_id', $userId);
                if ($groupId) {
                    $q->orWhere('group_id', $groupId);
                }
                if ($user->has_trial_access) {
                    $q->orWhere('is_trial', true);
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
        if ($this->is_trial) {
            return 'Пробный доступ';
        }

        return $this->user?->name ?? $this->group?->title ?? '—';
    }

    /**
     * IDs of parts served by active trial accesses — hidden from part
     * choice lists of regular (paid) accesses.
     *
     * @return int[]
     */
    public static function trialPartIds(): array
    {
        return TestAccessSubject::query()
            ->whereIn('test_access_id', self::query()->where('is_trial', true)->select('id'))
            ->whereNotNull('part_id')
            ->pluck('part_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
