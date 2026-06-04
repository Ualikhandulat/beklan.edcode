<?php

namespace App\Enums;


/*
 * Типы вопросов
 *
 * Формат IS_MATCH - соответствие:
 * var1: вопрос 1
 * var2: вопрос 2
 * var5: вариант 1
 * var6: вариант 2
 * var7: вариант 3
 * var8: вариант 4
 */
enum QuestionType: int
{
    case SELECT_ONE     = 0; // одно вариантный
    case SELECT_MULTI   = 1; // многовариантный
    case IS_GROUP       = 2; // контекст
    case IS_MATCH       = 3; // соответствие

    public function title(): string
    {
        return match ($this) {
            self::SELECT_ONE    => 'Одно вариантный',
            self::SELECT_MULTI  => 'Многовариантный',
            self::IS_GROUP      => 'Контекстный',
            self::IS_MATCH      => 'Соответствие',
        };
    }

    public function url(): string
    {
        return match ($this) {
            self::SELECT_ONE    => 'one',
            self::SELECT_MULTI  => 'multi',
            self::IS_GROUP      => 'group',
            self::IS_MATCH      => 'match',
        };
    }
}
