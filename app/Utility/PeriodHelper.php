<?php

namespace App\Utility;

use Carbon\Carbon;

/**
 * Formatting of billable periods — the "Y-m" months the collections module
 * bills, filters and batches by.
 *
 * Technical only: it knows how to write a period, never what the period is for.
 * The collections pages all offer the same \"which month\" select and write the
 * same month heading, so the conversion between the stored form and the written
 * form lives here once rather than in each service that needs it.
 */
class PeriodHelper
{
    /** @var string The storage format of a billable period. */
    const FORMAT = 'Y-m';

    /**
     * The current period, "Y-m".
     */
    public static function current(): string
    {
        return Carbon::now()->format(self::FORMAT);
    }

    /**
     * The period a date falls in, "Y-m" — a date, a stored date string or a
     * period already written that way. Null when there is no date to read.
     */
    public static function of($date): ?string
    {
        if (! $date) {
            return null;
        }
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->format(self::FORMAT);
        }
        $value = trim((string) $date);
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value;
        }
        try {
            return Carbon::parse($value)->format(self::FORMAT);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * The current period and the months leading up to it, as a select's
     * options: "2026-06" => "June 2026".
     *
     * @param  int  $back  How many months before the current one to include
     * @return array<string, string>
     */
    public static function recentMonths(int $back = 12): array
    {
        $options = [];

        $cursor = Carbon::now()->startOfMonth();

        for ($index = 0; $index <= $back; $index++) {
            $options[$cursor->format(self::FORMAT)] = $cursor->format('F Y');

            $cursor->subMonth();
        }

        return $options;
    }

    /**
     * Every period from one month to another, both ends included.
     *
     * @return array<int, string>
     */
    public static function between(string $from, string $to): array
    {
        $cursor = Carbon::createFromFormat(self::FORMAT, $from)->startOfMonth();
        $end = Carbon::createFromFormat(self::FORMAT, $to)->startOfMonth();
        $periods = [];

        while ($cursor->lessThanOrEqualTo($end)) {
            $periods[] = $cursor->format(self::FORMAT);

            $cursor->addMonth();
        }

        return $periods;
    }

    /**
     * The years a period-based table can be switched between: the years its
     * records fall in, plus the current one.
     *
     * @param  array<int, string>  $periods  Periods on file, "Y-m"
     * @return array<string, string>
     */
    public static function years(array $periods): array
    {
        $years = [];

        foreach ($periods as $period) {
            $year = substr((string) $period, 0, 4);

            if ($year !== '') {
                $years[$year] = $year;
            }
        }

        $current = Carbon::now()->format('Y');
        $years[$current] = $current;

        krsort($years);

        return $years;
    }

    /**
     * A period as prose: \"June 2026\". An empty period reads as everything.
     *
     * @param  string|null  $period
     */
    public static function label(?string $period): string
    {
        if (! $period) {
            return 'All periods';
        }

        return Carbon::createFromFormat(self::FORMAT, $period)->format('F Y');
    }

    /**
     * A period as its short heading: \"Jun 2026\". Used where a column heading
     * has to stay narrow.
     *
     * @param  string|null  $period
     */
    public static function shortLabel(?string $period): string
    {
        if (! $period) {
            return '—';
        }

        return Carbon::createFromFormat(self::FORMAT, $period)->format('M Y');
    }
}
