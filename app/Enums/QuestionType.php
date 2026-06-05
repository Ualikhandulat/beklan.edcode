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
enum QuestionType: string
{
    case SELECT_ONE = 'one';
    case SELECT_MULTI = 'multi';
    case IS_GROUP = 'group';
    case IS_MATCH = 'match';

    public function title(): string
    {
        return match ($this) {
            self::SELECT_ONE => 'Одно вариантный',
            self::SELECT_MULTI => 'Многовариантный',
            self::IS_GROUP => 'Контекстный',
            self::IS_MATCH => 'Соответствие',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SELECT_ONE => '#7C3AED',
            self::SELECT_MULTI => '#C026D3',
            self::IS_MATCH => '#0D9488',
            self::IS_GROUP => '#0891B2',
        };
    }
}
