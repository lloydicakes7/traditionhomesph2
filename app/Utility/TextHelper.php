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

    /**
     * The words of a search, so each one can be matched on its own.
     *
     * @return array<int, string>
     */
    public static function searchTerms(string $search): array
    {
        $terms = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY);

        return $terms === false ? [] : $terms;
    }
}
