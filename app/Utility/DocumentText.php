<?php

declare(strict_types=1);

namespace App\Utility;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * The text every printed document is built from.
 *
 * Technical only and stateless: it cleans a value that may be null, an array or
 * an object, shortens a value to the blank it has to fit on, splits a date into
 * the parts a paper form prints, wraps a paragraph onto ruled lines, and puts a
 * person's name together the way the forms write it. It knows no model and no
 * enum, so a document's data query can use it without the utility reading the
 * database.
 *
 * The one rule behind all of it: a missing value is an empty string, never the
 * word "null", a dash or an underscore run. The blank itself belongs to the
 * view's primitive, not to the value.
 */
final class DocumentText
{
    /** A non-breaking space, as DOM text rather than an entity. */
    const NBSP = "\u{00A0}";

    /**
     * A value as printable text: trimmed, whitespace collapsed, never null.
     *
     * "0" and real text survive untouched; null, an array, an object without a
     * string form and a value that is only whitespace all become ''. This is
     * the only place a document decides a value has nothing to print.
     *
     * @param  mixed  $value
     */
    public static function clean($value): string
    {
        if (is_string($value) || is_int($value) || is_float($value)) {
            return self::collapse((string) $value);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \Stringable) {
            return self::collapse((string) $value);
        }

        return '';
    }

    /**
     * A value shortened to the blank it has to fit on.
     *
     * The character budget comes from the blank's width in millimetres (about
     * two characters per 3.5 mm at 10 pt), so a long value never widens a blank
     * or pushes the rest of a row off the paper.
     */
    public static function fit(string $value, int $maxChars): string
    {
        $value = self::clean($value);

        if ($value === '' || $maxChars <= 0) {
            return '';
        }

        if (mb_strlen($value) <= $maxChars) {
            return $value;
        }

        if ($maxChars === 1) {
            return mb_substr($value, 0, 1);
        }

        return rtrim(mb_substr($value, 0, $maxChars - 1)) . '…';
    }

    /**
     * A date split into the parts a paper form prints.
     *
     * Every key always exists. A null, empty or unreadable value returns all
     * of them empty rather than a guess, which is what lets a blank "____ day
     * of ____" print as it was printed on the paper.
     *
     * @param  mixed  $value
     * @return array<string, string>
     */
    public static function dateParts($value): array
    {
        $empty = [
            'day'        => '',
            'dayOrdinal' => '',
            'month'      => '',
            'year'       => '',
            'yearShort'  => '',
            'long'       => '',
        ];

        $date = self::carbon($value);

        if (! $date) {
            return $empty;
        }

        $day = (int) $date->format('j');

        return [
            'day'        => (string) $day,
            'dayOrdinal' => $day . self::ordinalSuffix($day),
            'month'      => $date->format('F'),
            'year'       => $date->format('Y'),
            'yearShort'  => substr($date->format('Y'), -1),
            'long'       => $date->format('F j, Y'),
        ];
    }

    /**
     * A paragraph split into the lines that fit a ruled line.
     *
     * Returns every line the text needs, in order: the caller decides how many
     * lines the paper carries and pads or extends from this, so a long concern
     * is never cut short and never shrinks the type.
     *
     * @return array<int, string>
     */
    public static function wrap(string $text, int $charsPerLine, int $maxLines): array
    {
        $text = self::clean($text);
        $charsPerLine = max(1, $charsPerLine);
        $maxLines = max(1, $maxLines);

        if ($text === '') {
            return [];
        }

        $lines = [];

        foreach (explode("\n", wordwrap($text, $charsPerLine, "\n", false)) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // A single word longer than the line is broken by hand: wordwrap
            // refuses to cut a word, and a name of eighty characters must fit.
            while (mb_strlen($line) > $charsPerLine) {
                $lines[] = rtrim(mb_substr($line, 0, $charsPerLine));
                $line = trim(mb_substr($line, $charsPerLine));
            }

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * A unit written as the forms write it.
     *
     * @return array<string, string>
     */
    public static function blockLot(?int $block, ?int $lot): array
    {
        $blockText = $block === null ? '' : (string) $block;
        $lotText = $lot === null ? '' : (string) $lot;

        return [
            'block' => $blockText,
            'lot'   => $lotText,
            'label' => trim(self::join([
                $blockText === '' ? '' : 'Block ' . $blockText,
                $lotText === '' ? '' : 'Lot ' . $lotText,
            ])),
            'short' => trim(self::join([
                $blockText === '' ? '' : 'Blk ' . $blockText,
                $lotText === '' ? '' : 'Lot ' . $lotText,
            ])),
        ];
    }

    /**
     * A person's name as the forms write it: "Dela Cruz, Juan S."
     *
     * Never a stray comma and never a double space, whatever part is missing.
     */
    public static function person(
        ?string $surname,
        ?string $firstName = null,
        ?string $middleName = null,
        ?string $suffix = null,
        bool $middleInitial = false
    ): string {
        $surname = self::clean($surname);
        $first = self::clean($firstName);
        $middle = self::clean($middleName);
        $suffix = self::clean($suffix);

        if ($middle !== '' && $middleInitial) {
            $middle = mb_strtoupper(mb_substr($middle, 0, 1)) . '.';
        }

        $given = self::join([$first, $middle]);

        $name = $surname === '' ? $given : ($given === '' ? $surname : $surname . ', ' . $given);

        if ($suffix !== '') {
            $name = $name === '' ? $suffix : $name . ', ' . $suffix;
        }

        return $name;
    }

    /**
     * The parts joined by one space, with empty parts left out.
     *
     * @param  array<int, string>  $parts
     */
    public static function join(array $parts): string
    {
        return implode(' ', array_values(array_filter($parts, function ($part) {
            return trim((string) $part) !== '';
        })));
    }

    // =====================================================================
    // Internals
    // =====================================================================

    /**
     * Whitespace collapsed to single spaces and trimmed.
     */
    private static function collapse(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value);
        $value = preg_replace('/\s+/u', ' ', (string) $value);

        return trim((string) $value);
    }

    /**
     * A value read as a date, or null when it is empty or unreadable.
     *
     * @param  mixed  $value
     */
    private static function carbon($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        $value = self::clean($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * "1st", "2nd", "3rd", "4th", "11th".
     */
    private static function ordinalSuffix(int $day): string
    {
        if ($day % 100 >= 11 && $day % 100 <= 13) {
            return 'th';
        }

        return ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'][$day % 10];
    }
}
