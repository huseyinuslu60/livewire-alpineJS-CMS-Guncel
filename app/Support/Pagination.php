<?php

namespace App\Support;

class Pagination
{
    public static function clamp(?int $perPage, int $min = 5, int $max = 100, int $default = 20): int
    {
        $n = (int) ($perPage ?? $default);

        return max($min, min($n, $max));
    }
}
