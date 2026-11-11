<?php

declare(strict_types=1);

namespace App\Utility;

use App\Models\Property;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * The formatting the association's printed documents share.
 *
 * Technical only, like every other utility here: it knows how the association
 * writes a name, an address, a date and a checkbox, never what a document says
 * or which records it reads. Nothing here touches the database — a document's
 * builder hands it models and values it has already loaded, so a formatting
 * rule can never turn into a query.
 *
 * The rules themselves come from the paper forms; a builder that needs
 * "Block 4 Lot 12, Phase 2 Tradition Homes, …" or "30 ng Setyembre 2026" asks
 * for it here rather than assembling it again.
 */
final class PrintDocumentsHelper
{
    /**
     * The fill-in line a document prints where nothing was given.
     *
     * A printed form is filled in with a pen when the record does not carry the
     * fact, so a missing value is a line and never a guess.
     */
    const BLANK = '_______________';

    /**
     * The amount prefix the paper forms print.
     *
     * Not `MoneyHelper`'s currency symbol: the forms write "PHP 1,500.00", the
     * way the association's own booklets do.
     */
    const MONEY_PREFIX = 'PHP';

    /**
     * The months as the Sinumpaang Salaysay writes them.
     *
     * @var array<int, string>
     */
    const TAGALOG_MONTHS = [
        1  => 'Enero',
        2  => 'Pebrero',
        3  => 'Marso',
        4  => 'Abril',
        5  => 'Mayo',
        6  => 'Hunyo',
        7  => 'Hulyo',
        8  => 'Agosto',
        9  => 'Setyembre',
        10 => 'Oktubre',
        11 => 'Nobyembre',
        12 => 'Disyembre',
    ];

    /** What a time the document cannot read prints instead of a day part. */
    const DAY_PART_ANY = 'morning/afternoon/evening';

    /** The same, in the salaysay's own language. */
    const TAGALOG_DAY_PART_ANY = 'umaga/gabi/tanghali/hapon';

    // =====================================================================
    // Names
    // =====================================================================

    /**
     * A resident as most documents write them: "Juan S. Dela Cruz" — the
     * middle name as its initial, the suffix after a comma.
     */
    public static function displayName($resident): string
    {
        if (! $resident instanceof Resident) {
            return '';
        }

        $name = implode(' ', array_filter([
            $resident->first_name,
            self::initial($resident->middle_name),
            $resident->surname,
        ]));

        if ($resident->suffix) {
            $name .= ', ' . $resident->suffix;
        }

        return $name;
    }

    /**
     * The resident's full name as the record keeps it — "Juan Dela Cruz".
     */
    public static function fullName($resident): string
    {
        if (! $resident instanceof Resident) {
            return '';
        }

        $name = trim((string) $resident->full_name);

        return $name === '—' ? '' : $name;
    }

    /**
     * The name for a signature line: "JUAN S. DELA CRUZ".
     */
    public static function nameUpper($resident): string
    {
        return strtoupper(self::displayName($resident));
    }

    /**
     * A middle name as an initial: "Santos" → "S.".
     */
    public static function initial(?string $middleName): ?string
    {
        $middleName = trim((string) $middleName);

        return $middleName === '' ? null : strtoupper(substr($middleName, 0, 1)) . '.';
    }

    // =====================================================================
    // Addresses
    // =====================================================================

    /**
     * The one canonical unit address every document prints:
     * "Block 4 Lot 12, Phase 2 Tradition Homes, Barangay Inocencio, Trece
     * Martires City, Cavite".
     *
     * The community it stands in comes from configuration, so a document
     * never rebuilds it from address parts — and the office's own block and
     * lot stay out of a resident's address.
     */
    public static function unitAddress($property): string
    {
        if (! $property instanceof Property) {
            return '';
        }

        return trim(implode(', ', array_filter([
            self::blockLotSpelled($property),
            trim((string) config('thha.community', '')),
        ])));
    }

    /**
     * The short unit label the residents list files a row under:
     * "Blk 4 · Lot 12".
     */
    public static function blockLot($property): string
    {
        if (! $property instanceof Property) {
            return '';
        }

        $parts = array_filter([
            self::label('Blk', $property->block),
            self::label('Lot', $property->lot),
        ]);

        return implode(' · ', $parts);
    }

    /**
     * The unit written out, as a sentence needs it: "Block 4 Lot 12".
     */
    private static function blockLotSpelled(Property $property): string
    {
        $parts = array_filter([
            self::label('Block', $property->block),
            self::label('Lot', $property->lot),
        ]);

        return implode(' ', $parts);
    }

    /**
     * "Block 4" — a label and its value, or nothing when there is no value.
     */
    private static function label(string $label, $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $label . ' ' . $value;
    }

    // =====================================================================
    // Dates and times
    // =====================================================================

    /**
     * A date as the forms write it: "September 30, 2026".
     */
    public static function date($value): string
    {
        $date = self::carbon($value);

        return $date ? $date->format('F j, Y') : '';
    }

    /**
     * A date the short way: "Sep 30, 2026" — the ledger and the notice.
     */
    public static function shortDate($value): string
    {
        $date = self::carbon($value);

        return $date ? $date->format('M d, Y') : '';
    }

    /**
     * A date and a clock time: "Sep 25, 2026 9:30 PM".
     */
    public static function dateTime($value): string
    {
        $date = self::carbon($value);

        return $date ? $date->format('M d, Y g:i A') : '';
    }

    /**
     * A date and a clock time as a notice stamps them:
     * "Sep 25, 2026 9:30 PM".
     *
     * The date and the time are separate columns on the record, so they are
     * read as a pair and written as one stamp.
     */
    public static function stamp($date, $time = null): string
    {
        $written = self::shortDate($date);

        if ($written === '') {
            return '';
        }

        $parts = self::timeParts($time !== null ? $time : $date);

        if ($parts === null || ($time === null && $parts[0] === 0 && $parts[1] === 0)) {
            return $written;
        }

        return $written . ' ' . Carbon::createFromTime($parts[0], $parts[1])->format('g:i A');
    }

    /**
     * The day of the month as a sentence writes it: "30th".
     */
    public static function ordinalDay($value): string
    {
        $date = self::carbon($value);

        if (! $date) {
            return '';
        }

        $day = (int) $date->format('j');

        $suffix = ($day % 100 >= 11 && $day % 100 <= 13)
            ? 'th'
            : ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'][$day % 10];

        return $day . $suffix;
    }

    /**
     * A span of days as a permit writes it, with the year said once when both
     * ends share it:
     *
     *   "October 5 to October 19, 2026"
     *   "October 28 to November 2, 2026"
     *   "December 30, 2026 to January 3, 2027"
     *   "October 5, 2026"                    (one day, or no end given)
     */
    public static function dateRange($from, $to = null): string
    {
        $start = self::carbon($from);
        $end = self::carbon($to);

        if (! $start) {
            return '';
        }

        if (! $end || $start->isSameDay($end)) {
            return $start->format('F j, Y');
        }

        $left = $start->year === $end->year
            ? $start->format('F j')
            : $start->format('F j, Y');

        return $left . ' to ' . $end->format('F j, Y');
    }

    /**
     * The part of the day a clock time falls in, as the invitation says it:
     * before noon morning, to 5:59 PM afternoon, from 6:00 PM evening. A time
     * the document cannot read prints all three rather than guessing one.
     */
    public static function dayPart($time): string
    {
        $hour = self::hour($time);

        if ($hour === null) {
            return self::DAY_PART_ANY;
        }

        if ($hour < 12) {
            return 'morning';
        }

        return $hour < 18 ? 'afternoon' : 'evening';
    }

    /**
     * The month in Filipino: "Setyembre".
     */
    public static function tagalogMonth($value): string
    {
        $date = self::carbon($value);

        return $date ? self::TAGALOG_MONTHS[(int) $date->format('n')] : '';
    }

    /**
     * A date as the salaysay words it: "30 ng Setyembre 2026".
     */
    public static function tagalogDate($value): string
    {
        $date = self::carbon($value);

        if (! $date) {
            return '';
        }

        return $date->format('j') . ' ng ' . self::tagalogMonth($date) . ' ' . $date->format('Y');
    }

    /**
     * The part of the day in Filipino: before 5:00 AM gabi, to 10:59 AM umaga,
     * to 12:59 PM tanghali, to 5:59 PM hapon, from 6:00 PM gabi.
     */
    public static function tagalogDayPart($time): string
    {
        $hour = self::hour($time);

        if ($hour === null) {
            return self::TAGALOG_DAY_PART_ANY;
        }

        if ($hour < 5) {
            return 'gabi';
        }

        if ($hour < 11) {
            return 'umaga';
        }

        if ($hour < 13) {
            return 'tanghali';
        }

        return $hour < 18 ? 'hapon' : 'gabi';
    }

    /**
     * A clock time the way a letter writes it: "3:00 PM".
     */
    public static function time12($time): string
    {
        $parts = self::timeParts($time);

        if ($parts === null) {
            return trim((string) $time);
        }

        return Carbon::createFromTime($parts[0], $parts[1])->format('g:i A');
    }

    /**
     * A clock time the way a Filipino sentence writes it: "9:30".
     *
     * The salaysay states the hour and then names the part of the day ("9:30
     * ng gabi"), so the meridiem is left off here — the day part that follows
     * it is the meridiem.
     */
    public static function clockTime($time): string
    {
        $parts = self::timeParts($time);

        if ($parts === null) {
            return trim((string) $time);
        }

        return Carbon::createFromTime($parts[0], $parts[1])->format('g:i');
    }

    /**
     * The hour of a clock time, or null when it cannot be read.
     *
     * A time reaches a document as an "H:i" string from a form, as a database
     * time, or as a Carbon instance — all three are understood here so no
     * document has to know which it was handed.
     */
    private static function hour($time): ?int
    {
        $parts = self::timeParts($time);

        return $parts === null ? null : $parts[0];
    }

    /**
     * The hour and minute of a clock time, or null when it cannot be read.
     *
     * @return array<int, int>|null
     */
    private static function timeParts($time): ?array
    {
        if ($time instanceof \DateTimeInterface) {
            return [(int) $time->format('G'), (int) $time->format('i')];
        }

        $time = trim((string) $time);

        if ($time === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AP]\.?M\.?)?$/i', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $meridiem = isset($matches[3]) ? strtoupper(str_replace('.', '', $matches[3])) : null;

            if ($meridiem === 'PM' && $hour < 12) {
                $hour += 12;
            }

            if ($meridiem === 'AM' && $hour === 12) {
                $hour = 0;
            }

            return $hour > 23 || $minute > 59 ? null : [$hour, $minute];
        }

        try {
            $parsed = Carbon::parse($time);
        } catch (\Exception $e) {
            return null;
        }

        return [(int) $parsed->format('G'), (int) $parsed->format('i')];
    }

    /**
     * A value read as a date, or null when it is empty or unreadable.
     */
    private static function carbon($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $value = trim((string) $value);

        if ($value === '' || $value === self::BLANK) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // =====================================================================
    // Words
    // =====================================================================

    /**
     * How a Filipino sentence says a resident's civil status.
     *
     * The salaysay states it in Filipino and states it to a room, so a status
     * the record does not carry prints every word it might have been rather
     * than a blank.
     */
    public static function civilStatusPhrase($resident): string
    {
        $status = $resident instanceof Resident
            ? strtolower(trim((string) $resident->civil_status))
            : '';

        $female = $resident instanceof Resident && (string) $resident->sex === 'female';

        switch ($status) {
            case 'single':
                return $female ? 'dalaga' : 'binata';

            case 'married':
                return 'may-asawa';

            case 'widowed':
                return $female ? 'biyuda' : 'biyudo';

            case 'separated':
            case 'divorced':
                return 'hiwalay';
        }

        return 'may-asawa/binata/dalaga, biyuda';
    }

    /**
     * A noun with the right article: "a meeting", "an orientation".
     */
    public static function withArticle(string $noun): string
    {
        $noun = trim($noun);

        if ($noun === '') {
            return '';
        }

        return (stripos('aeiou', $noun[0]) !== false ? 'an ' : 'a ') . $noun;
    }

    /**
     * A block of text split into paragraphs — one line of the form, one
     * paragraph.
     *
     * @return array<int, string>
     */
    public static function paragraphs(?string $text): array
    {
        $text = trim((string) $text);

        if ($text === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $lines), function ($line) {
            return $line !== '';
        }));
    }

    /**
     * Paragraphs wrapped to the width of a ruled line, one entry per ruled line
     * with a blank line between paragraphs.
     *
     * A salaysay is written by hand onto ruled lines, so the wrapping here is
     * what decides how many lines the narrator takes up — and how many are left
     * ruled and empty.
     *
     * @param  array<int, string>  $paragraphs
     * @return array<int, string>
     */
    public static function wrapLines(array $paragraphs, int $width = 90): array
    {
        $width = max(10, $width);
        $lines = [];

        foreach ($paragraphs as $index => $paragraph) {
            $paragraph = trim((string) $paragraph);

            if ($paragraph === '') {
                continue;
            }

            if ($index > 0 && ! empty($lines)) {
                $lines[] = '';
            }

            foreach (explode("\n", wordwrap($paragraph, $width, "\n", false)) as $line) {
                $lines[] = rtrim($line);
            }
        }

        return $lines;
    }

    // =====================================================================
    // Checkbox groups
    // =====================================================================

    /**
     * One checkbox or tick-list option, as every view reads it.
     *
     * The shape `options()` and `optionsFromEnum()` build their groups from,
     * and what a builder that ticks several boxes at once (the RBI form's
     * sectoral information) builds its own rows with.
     *
     * @return array<string, mixed>
     */
    public static function option(
        string $label,
        bool $checked = false,
        string $suffix = '',
        bool $free = false,
        ?string $text = null
    ): array {
        return [
            'label'   => $label,
            'checked' => $checked,
            'free'    => $free,
            'text'    => $free ? trim((string) $text) : '',
            'suffix'  => $suffix,
        ];
    }

    /**
     * A checkbox group, as every view reads it.
     *
     * Each option is `['label' => …, 'checked' => bool, 'free' => bool,
     * 'text' => …, 'suffix' => …]`. The label is what the paper prints, `free`
     * marks the one option that also carries a fill-in line ("Others: ______"),
     * and `text` is what goes on that line. A label given as an array carries
     * the option's own `suffix` and can mark itself free:
     *
     *   PrintDocumentsHelper::options(
     *       ['owner' => 'Owner', 'others' => ['label' => 'Others', 'free' => true]],
     *       $selected,
     *       'others',
     *       $writtenIn
     *   );
     *
     * @param  array<string, string|array<string, mixed>>  $labels    value => label
     * @param  mixed                                       $selected  the chosen value, if any
     * @param  string|null                                 $freeLabel the option that carries a fill-in line
     * @param  string|null                                 $freeText  what is written on it
     * @return array<int, array<string, mixed>>
     */
    public static function options(
        array $labels,
        $selected = null,
        ?string $freeLabel = null,
        ?string $freeText = null
    ): array {
        $options = [];

        foreach ($labels as $value => $definition) {
            $label = is_array($definition)
                ? (string) ($definition['label'] ?? $value)
                : (string) $definition;

            $suffix = is_array($definition) ? (string) ($definition['suffix'] ?? '') : '';

            $free = (is_array($definition) && ! empty($definition['free']))
                || ($freeLabel !== null && ((string) $value === $freeLabel || $label === $freeLabel));

            $options[] = self::option(
                $label,
                $selected !== null && $selected !== '' && (string) $selected === (string) $value,
                $suffix,
                $free,
                $freeText
            );
        }

        return $options;
    }

    /**
     * A checkbox group built from an enum's cases, so the form's tick-list and
     * the printed form cannot drift apart.
     *
     * A case whose own label states free text (a business permit's "Other")
     * carries the fill-in line.
     *
     * @param  array<int, \App\Enums\Enum>  $cases
     * @param  mixed                        $selected
     * @return array<int, array<string, mixed>>
     */
    public static function optionsFromEnum(array $cases, $selected = null, ?string $freeText = null): array
    {
        $options = [];

        foreach ($cases as $case) {
            $free = method_exists($case, 'hasFreeText') && $case->hasFreeText();

            $options[] = self::option(
                (string) $case->label(),
                $selected !== null && $selected !== '' && (string) $selected === (string) $case->value,
                '',
                $free,
                $freeText
            );
        }

        return $options;
    }

    // =====================================================================
    // Values
    // =====================================================================

    /**
     * The value a document prints, or the fill-in line when there is none.
     */
    public static function blank(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? self::BLANK : $value;
    }

    /**
     * An amount as the paper forms write it: "PHP 1,500.00".
     */
    public static function money($amount, bool $dashWhenZero = false): string
    {
        $amount = (float) $amount;

        if ($dashWhenZero && $amount === 0.0) {
            return '—';
        }

        return self::MONEY_PREFIX . ' ' . number_format($amount, 2);
    }

    /**
     * The first of the candidate columns the model actually carries, as text.
     *
     * The paper forms ask for a good deal the association's records do not keep
     * yet (a PhilSys number, a blood type, a sector flag). Those are read
     * through here: the first of the names the record uses is printed, and a
     * record that has none of them prints a fill-in line instead of failing —
     * a missing column is not an error on a form that is filled in by hand.
     * A boolean column answers "Yes"/"No", the way a tick-box reads.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @param  array<int, string>                        $candidateColumns
     */
    public static function attribute($model, array $candidateColumns): string
    {
        if (! $model instanceof Model) {
            return '';
        }

        $attributes = $model->getAttributes();

        foreach ($candidateColumns as $column) {
            if (! array_key_exists($column, $attributes)) {
                continue;
            }

            $value = $model->getAttribute($column);

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            return trim((string) $value);
        }

        return '';
    }

    /**
     * Whether a value — from a form, a setting or a record — means "yes".
     */
    public static function flag($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * The next number of its kind, in the association's shape: "RBI-2026-00001".
     *
     * Nothing counts these — the office keeps its own register — so a document
     * that asks for one is offered the year's first number to accept or
     * overwrite. Never invented onto a record that has its own number.
     */
    public static function suggestedNumber(string $prefix = 'RBI', int $padding = 4): string
    {
        return $prefix . '-' . date('Y') . '-' . str_pad('1', max(1, $padding), '0', STR_PAD_LEFT);
    }
}
