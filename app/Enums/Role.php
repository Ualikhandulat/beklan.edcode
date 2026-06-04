<?php

namespace App\Enums;

enum Role: string
{
    case Administrator  = 'administrator';
    case Student        = 'student';

    public function title(): string
    {
        return match ($this) {
            self::Administrator => "Администратор",
            self::Student       => "Ученик(-ца)",
        };
    }
}
