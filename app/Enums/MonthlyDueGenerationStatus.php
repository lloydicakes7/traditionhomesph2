<?php

namespace App\Enums;

/**
 * Where a unit stands for one billing month, as the generator reads it.
 *
 * A month is billed for every property with a household; the generator opens on
 * the whole list so the officer can see what pressing Generate will still
 * raise. A unit with the month's invoice already carries its own bill status —
 * unpaid, partial, paid — and this enum says whether that invoice exists and
 * whether it is settled.
 *
 * @method static MonthlyDueGenerationStatus TO_BILL()
 * @method static MonthlyDueGenerationStatus BILLED()
 * @method static MonthlyDueGenerationStatus PAID()
 */
class MonthlyDueGenerationStatus extends Enum
{
    const TO_BILL = 'to_bill';
    const BILLED  = 'billed';
    const PAID    = 'paid';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::TO_BILL => 'To bill',
            self::BILLED  => 'Billed',
            self::PAID    => 'Paid',
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
     * AdminLTE badge theme class for this status.
     */
    public function badgeTheme(): string
    {
        return [
            self::TO_BILL => 'warning',
            self::BILLED  => 'info',
            self::PAID    => 'success',
        ][$this->value] ?? 'secondary';
    }
}
