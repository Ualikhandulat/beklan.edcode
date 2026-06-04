<?php

namespace App\Enums;

enum Role: string
{
    case Admin      = 'administrator';
    case Student    = 'student';

    public function title(): string
    {
        return match ($this) {
            self::Admin     => "Администратор",
            self::Student   => "Ученик(-ца)",
        };
    }
}
