<?php

namespace App\Enums;

/**
 * What a home-based business permit is being issued for.
 *
 * The permit form's own tick-list, so the association's paper form and the
 * print page's select read the same fixed set: the enum states the options,
 * the select offers them, and the PDF ticks the one that was chosen. "Other"
 * is a member of the set and is what lets the officer write the purpose the
 * list does not carry.
 *
 * @method static BusinessPermitPurpose BUSINESS_PERMIT()
 * @method static BusinessPermitPurpose FRANCHISING()
 * @method static BusinessPermitPurpose INTERNET_CONNECTION()
 * @method static BusinessPermitPurpose ELECTRICAL_CONNECTION()
 * @method static BusinessPermitPurpose WATER_CONNECTION()
 * @method static BusinessPermitPurpose OTHER()
 */
class BusinessPermitPurpose extends Enum
{
    const BUSINESS_PERMIT       = 'business_permit';
    const FRANCHISING           = 'franchising';
    const INTERNET_CONNECTION   = 'internet_connection';
    const ELECTRICAL_CONNECTION = 'electrical_connection';
    const WATER_CONNECTION      = 'water_connection';
    const OTHER                 = 'other';

    /**
     * Map of value → human-readable label, in the paper form's own order.
     *
     * The permit prints its purposes in two columns, filled left to right, so
     * the order here is the order on the paper: Business Permit beside
     * Electrical Connection, Franchising beside Water Connection, Internet
     * Connection beside Other. The select a print page offers reads the same
     * list, so the screen and the paper cannot list them differently.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::BUSINESS_PERMIT       => 'Business Permit',
            self::ELECTRICAL_CONNECTION => 'Electrical Connection',
            self::FRANCHISING           => 'Franchising',
            self::WATER_CONNECTION      => 'Water Connection',
            self::INTERNET_CONNECTION   => 'Internet Connection',
            self::OTHER                 => 'Other',
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
     * Whether this purpose needs its own written-out text.
     */
    public function hasFreeText(): bool
    {
        return $this->value === self::OTHER;
    }
}
