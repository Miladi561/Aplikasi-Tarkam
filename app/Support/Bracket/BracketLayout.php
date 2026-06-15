<?php

namespace App\Support\Bracket;

class BracketLayout
{
    public static function matchGapRem(int $roundOrder): float
    {
        return min(9.5, 1.25 * (2 ** max(0, $roundOrder - 1)));
    }

    public static function columnOffsetRem(int $roundOrder): float
    {
        return min(8.0, 1.75 * max(0, $roundOrder - 1));
    }

    public static function connectorHeightRem(int $roundOrder): float
    {
        return min(8.5, 1.05 * (2 ** max(0, $roundOrder - 1)));
    }
}
