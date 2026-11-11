<?php

namespace App\Enums;

/**
 * How a percentage-or-amount adjustment is applied.
 *
 * One enum, because a discount and a penalty are the same computation in
 * opposite directions: both are either a share of the amount they are charged
 * against or a flat figure. The rate computation lives here so the settings
 * page and the generator price an adjustment the same way.
 *
 * @method static AdjustmentType PERCENT()
 * @method static AdjustmentType FIXED()
 */
class AdjustmentType extends Enum
{
    const PERCENT = 'percent';
    const FIXED   = 'fixed';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PERCENT => 'Percentage (%)',
            self::FIXED   => 'Fixed Amount',
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
     * The adjustment this type and value produce against a base amount.
     *
     * @param  float  $base   The amount the adjustment is charged against
     * @param  float  $value  The percentage or flat figure configured
     * @return float
     */
    public function compute(float $base, float $value): float
    {
        if ($this->value === self::PERCENT) {
            return round($base * $value / 100, 2);
        }

        return round($value, 2);
    }

    /**
     * How a configured value of this type reads — "5%", "₱100.00".
     */
    public function display(float $value): string
    {
        return $this->value === self::PERCENT
            ? rtrim(rtrim(number_format($value, 2), '0'), '.') . '%'
            : \App\Utility\MoneyHelper::format($value);
    }
}
