<?php

namespace App\Enums;

enum TestAccessType: string
{
    case Ent = 'ent';
    case Subject = 'subject';

    public function label(): string
    {
        return match ($this) {
            self::Ent => 'ЕНТ',
            self::Subject => 'Предмет',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Ent => 'badge-warning',
            self::Subject => 'badge-info',
        };
    }
}
