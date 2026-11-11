<?php

namespace App\Enums;

use Carbon\Carbon;

/**
 * The days of the week an amenity keeps its schedule by.
 *
 * Values are ISO-8601 weekday numbers (1 = Monday … 7 = Sunday), which is what
 * Carbon reports and what makes the week read Monday-first wherever it is
 * listed or grouped.
 *
 * @method static Weekday MONDAY()
 * @method static Weekday TUESDAY()
 * @method static Weekday WEDNESDAY()
 * @method static Weekday THURSDAY()
 * @method static Weekday FRIDAY()
 * @method static Weekday SATURDAY()
 * @method static Weekday SUNDAY()
 */
class Weekday extends Enum
{
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;
    const SUNDAY    = 7;

    /**
     * Map of value → full day name.
     *
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return [
            self::MONDAY    => 'Monday',
            self::TUESDAY   => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY  => 'Thursday',
            self::FRIDAY    => 'Friday',
            self::SATURDAY  => 'Saturday',
            self::SUNDAY    => 'Sunday',
        ];
    }

    /**
     * Map of value → three-letter day name, for tight spots (schedule
     * summaries, calendar headers).
     *
     * @return array<int, string>
     */
    public static function shortLabels(): array
    {
        return [
            self::MONDAY    => 'Mon',
            self::TUESDAY   => 'Tue',
            self::WEDNESDAY => 'Wed',
            self::THURSDAY  => 'Thu',
            self::FRIDAY    => 'Fri',
            self::SATURDAY  => 'Sat',
            self::SUNDAY    => 'Sun',
        ];
    }

    /**
     * The weekday a date falls on.
     *
     * @param  \Carbon\Carbon|string  $date
     * @return static
     */
    public static function of($date): self
    {
        return self::from(Carbon::parse($date)->isoWeekday());
    }

    /**
     * Full day name for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? (string) $this->value;
    }

    /**
     * Three-letter day name for this instance.
     */
    public function shortLabel(): string
    {
        return self::shortLabels()[$this->value] ?? (string) $this->value;
    }
}
