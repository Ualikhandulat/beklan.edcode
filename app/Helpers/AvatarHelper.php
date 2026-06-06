<?php

namespace App\Helpers;

class AvatarHelper
{
    /** @var string[] */
    private static array $colors = [
        '#E53E3E', '#DD6B20', '#D69E2E', '#38A169',
        '#3182CE', '#805AD5', '#D53F8C', '#319795',
        '#2F855A', '#2B6CB0', '#6B46C1', '#C05621',
    ];

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

    /**
     * Pick a saturated color deterministically from the name.
     * Algorithm mirrors the JS avatarColor() in groups/show.blade.php.
     */
    public static function color(string $name): string
    {
        $count = count(self::$colors);
        $sum = 0;

        foreach (mb_str_split($name) as $char) {
            $sum += mb_ord($char);
        }

        return self::$colors[$sum % $count];
    }
}
