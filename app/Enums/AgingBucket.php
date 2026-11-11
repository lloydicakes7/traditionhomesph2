<?php

namespace App\Enums;

/**
 * How long a balance has been outstanding — the buckets the AR aging report
 * files by.
 *
 * The bucket owns its label and the colour it is painted in, so the report
 * shows the same bucket the same way everywhere without repeating the mapping.
 *
 * "Not yet due" is a bucket of its own: a bill inside its grace period is
 * outstanding, but it is not overdue, and the office reads the two differently.
 *
 * @method static AgingBucket NOT_DUE()
 * @method static AgingBucket DAYS_1_30()
 * @method static AgingBucket DAYS_31_60()
 * @method static AgingBucket DAYS_61_90()
 * @method static AgingBucket DAYS_91_PLUS()
 */
class AgingBucket extends Enum
{
    const NOT_DUE    = 'current';
    const DAYS_1_30  = '1_30';
    const DAYS_31_60 = '31_60';
    const DAYS_61_90 = '61_90';
    const DAYS_91_PLUS = '91_plus';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::NOT_DUE      => 'Not Yet Due',
            self::DAYS_1_30    => '1-30 Days',
            self::DAYS_31_60   => '31-60 Days',
            self::DAYS_61_90   => '61-90 Days',
            self::DAYS_91_PLUS => '91+ Days',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * AdminLTE theme class for this bucket — the same bucket reads the same
     * colour wherever it is shown.
     */
    public function badgeTheme(): string
    {
        return [
            self::NOT_DUE      => 'secondary',
            self::DAYS_1_30    => 'warning',
            self::DAYS_31_60   => 'danger',
            self::DAYS_61_90   => 'dark',
            self::DAYS_91_PLUS => 'primary',
        ][$this->value] ?? 'secondary';
    }

    /**
     * How much of a bucket is overdue money — the buckets the reports count as
     * arrears, since a balance not yet due has not been missed.
     */
    public function isOverdue(): bool
    {
        return $this->value !== self::NOT_DUE;
    }

    /**
     * The bucket a number of days outstanding falls in.
     */
    public static function forDays(int $days): self
    {
        if ($days <= 0) {
            return self::NOT_DUE();
        }

        if ($days <= 30) {
            return self::DAYS_1_30();
        }

        if ($days <= 60) {
            return self::DAYS_31_60();
        }

        if ($days <= 90) {
            return self::DAYS_61_90();
        }

        return self::DAYS_91_PLUS();
    }

    /**
     * Every bucket, in the order the reports read them, as a select's options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
