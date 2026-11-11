<?php

namespace App\Utility;

use Carbon\CarbonInterface;

/**
 * Formatting of clock times and time windows, the way the association writes
 * them — an opening window on an amenity card, the hours of a reservation in
 * the bookings table.
 *
 * Technical only: it knows how to write a time, never what the time is for.
 * A time-only column (an amenity's opening hour) is read back as a string and
 * parsed here, so there is one place that decides what "8 AM" looks like.
 */
class TimeHelper
{
    /**
     * A time the short way: "8AM", "10PM", and "8:30AM" when it is not on the
     * hour. Used on cards and anywhere the window itself is the message.
     *
     * @param  \Carbon\CarbonInterface|string|null  $time
     * @return string
     */
    public static function short($time): string
    {
        if ($time === null || $time === '') {
            return '';
        }

        $time = $time instanceof CarbonInterface ? $time : self::parse($time);

        return $time->minute === 0
            ? $time->format('gA')
            : $time->format('g:iA');
    }

    /**
     * A time the way a record writes it: "8:00 AM", "10:30 PM".
     *
     * @param  \Carbon\CarbonInterface|string|null  $time
     * @return string
     */
    public static function clock($time): string
    {
        if ($time === null || $time === '') {
            return '';
        }

        $time = $time instanceof CarbonInterface ? $time : self::parse($time);

        return $time->format('g:i A');
    }

    /**
     * A window the short way: "2–6 PM" when both ends share a meridiem,
     * "11 AM – 1 PM" when they do not.
     *
     * @param  \Carbon\CarbonInterface|string|null  $from
     * @param  \Carbon\CarbonInterface|string|null  $to
     * @return string
     */
    public static function window($from, $to): string
    {
        if ($from === null || $to === null || $from === '' || $to === '') {
            return '';
        }

        $from = $from instanceof CarbonInterface ? $from : self::parse($from);
        $to = $to instanceof CarbonInterface ? $to : self::parse($to);

        if ($from->format('A') === $to->format('A')) {
            $start = $from->minute === 0 ? $from->format('g') : $from->format('g:i');

            return $start . '–' . self::short($to);
        }

        return self::short($from) . ' – ' . self::short($to);
    }

    /**
     * A window the way a record writes it: "8:00 AM – 10:00 PM".
     *
     * @param  \Carbon\CarbonInterface|string|null  $from
     * @param  \Carbon\CarbonInterface|string|null  $to
     * @return string
     */
    public static function clockWindow($from, $to): string
    {
        if ($from === null || $to === null || $from === '' || $to === '') {
            return '';
        }

        return self::clock($from) . ' – ' . self::clock($to);
    }

    /**
     * Parse a stored time ("08:00:00", "08:00") onto today's date, so a
     * time-only column behaves like a time wherever it is formatted.
     *
     * @param  string  $time
     * @return \Carbon\Carbon
     */
    private static function parse(string $time): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($time);
    }
}
