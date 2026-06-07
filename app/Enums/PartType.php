<?php

namespace App\Enums;

enum PartType: string
{
    case Topic = 'topic';
    case Nusqa = 'nusqa';

    public function label(): string
    {
        return match ($this) {
            self::Topic => 'Тақырып',
            self::Nusqa => 'Нұсқа',
        };
    }

    public function labelPlural(): string
    {
        return match ($this) {
            self::Topic => 'Тақырыптар',
            self::Nusqa => 'Нұсқалар',
        };
    }

    public function createLabel(): string
    {
        return match ($this) {
            self::Topic => 'Добавить тақырып',
            self::Nusqa => 'Добавить нұсқа',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Topic => 'badge-info',
            self::Nusqa => 'badge-primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Topic => 'book-open',
            self::Nusqa => 'clipboard-list',
        };
    }
}
