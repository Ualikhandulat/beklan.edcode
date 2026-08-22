<?php

namespace App\Services;

use App\Enums\PartType;
use App\Enums\TestAccessType;
use App\Models\Part;
use App\Models\TestAccess;

/**
 * Управляет единым пробным доступом через галочку «Пробный нұсқа» в форме нұсқа:
 * назначенный нұсқа становится содержимым пробного теста для пользователей
 * с has_trial_access (саморегистрация или ручное открытие админом).
 */
class TrialAccessService
{
    /** ID нұсқа, назначенного пробным (через активный пробный доступ). */
    public function trialPartId(): ?int
    {
        return TestAccess::where('is_trial', true)
            ->where('is_active', true)
            ->latest('id')
            ->first()
            ?->accessSubjects->first()
            ?->part_id;
    }

    /**
     * Назначить нұсқа пробным: создать единый пробный доступ или перенастроить
     * существующий (1 попытка, все вопросы нұсқа, без лимита времени по умолчанию).
     */
    public function assignPart(Part $part): void
    {
        $access = TestAccess::where('is_trial', true)->latest('id')->first();

        if ($access) {
            $access->update(['type' => TestAccessType::Subject, 'is_active' => true]);
        } else {
            $access = TestAccess::create([
                'type' => TestAccessType::Subject,
                'is_trial' => true,
                'attempts_limit' => 1,
                'question_count' => 0,
            ]);
        }

        $access->accessSubjects()->delete();
        $access->accessSubjects()->create([
            'subject_id' => $part->subject_id,
            'part_type' => PartType::Nusqa->value,
            'part_id' => $part->id,
        ]);

        TestAccess::where('is_trial', true)
            ->whereKeyNot($access->id)
            ->update(['is_active' => false]);
    }

    /** Снять пробный статус с нұсқа: деактивировать пробный доступ, указывающий на него. */
    public function unassignPart(Part $part): void
    {
        TestAccess::where('is_trial', true)
            ->where('is_active', true)
            ->whereHas('accessSubjects', fn ($q) => $q->where('part_id', $part->id))
            ->update(['is_active' => false]);
    }
}
