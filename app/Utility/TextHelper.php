<?php

namespace App\Utility;

class TextHelper
{
    /**
     * The text as the association stores it: single-spaced, trimmed, uppercase.
     */
    public static function upper(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', trim($value));

        if ($normalized === null || $normalized === '') {
            return null;
        }

        return mb_strtoupper($normalized, 'UTF-8');
    }
}
