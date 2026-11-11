<?php

namespace App\Enums;

/**
 * How an amenity charges for its use — the fixed set of pricing models the
 * association books its facilities under: a flat rate per booking, or a rate
 * per hour booked.
 *
 * @method static AmenityRateType HOURLY()
 * @method static AmenityRateType FIXED()
 */
class AmenityRateType extends Enum
{
    const HOURLY = 'hourly';
    const FIXED  = 'fixed';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::FIXED  => 'Fixed',
            self::HOURLY => 'Hourly',
        ];
    }

    /**
     * Map of value → short description of what the rate buys.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::FIXED  => 'One flat rate per booking',
            self::HOURLY => 'Charged per hour booked',
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
     * What this rate is charged per, ready to sit after an amount — the
     * " / hr" in "₱120 / hr". A flat rate is charged once, so it carries no
     * suffix.
     */
    public function unitSuffix(): string
    {
        return [
            self::HOURLY => ' / hr',
            self::FIXED  => '',
        ][$this->value] ?? '';
    }

    /**
     * Short description of what the rate buys, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }
}
