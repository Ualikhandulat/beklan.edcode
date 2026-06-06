<?php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * Generate initials from a name.
     *
     * @param  bool  $singleLetter  For groups — return only first letter
     */
    public static function initials(string $name, bool $singleLetter = false): string
    {
        $name = trim($name);

        if ($singleLetter) {
            return mb_strtoupper(mb_substr($name, 0, 1));
        }

        $words = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    /** @var string[] */
    private static array $colors = [
        '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16',
        '#22C55E', '#10B981', '#14B8A6', '#06B6D4', '#0EA5E9',
        '#3B82F6', '#6366F1', '#8B5CF6', '#A855F7', '#D946EF',
        '#EC4899', '#F43F5E', '#DC2626', '#EA580C', '#D97706',
        '#CA8A04', '#65A30D', '#16A34A', '#059669', '#0D9488',
        '#0891B2', '#0284C7', '#2563EB', '#7C3AED', '#9333EA',
    ];

    /**
     * Generate a two-color gradient deterministically from the name.
     * Picks two colors from the palette spaced 15 apart for contrast.
     * Algorithm mirrors the JS avatarColor() in groups/show.blade.php.
     */
    public static function color(string $name): string
    {
        $sum = 0;

        foreach (mb_str_split($name) as $char) {
            $sum += mb_ord($char);
        }

        $count = count(self::$colors);
        $c1 = self::$colors[$sum % $count];
        $c2 = self::$colors[($sum + 15) % $count];

        return "linear-gradient(135deg, {$c1}, {$c2})";
    }
}
