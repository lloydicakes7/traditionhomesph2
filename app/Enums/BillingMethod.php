<?php

namespace App\Enums;

/**
 * How the association computes a property's monthly due.
 *
 * The billing method is a configuration choice that changes what a due is made
 * of: a rate charged per square metre of the property, or one fixed rate per
 * property. Everything that prices a month — the generator, the rate cards,
 * the computed due — reads the method from here, so the rule is written once.
 *
 * @method static BillingMethod PER_SQM()
 * @method static BillingMethod FIXED_RATE()
 */
class BillingMethod extends Enum
{
    const PER_SQM    = 'per_sqm';
    const FIXED_RATE = 'fixed_rate';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PER_SQM    => 'Per SQM',
            self::FIXED_RATE => 'Fixed Rate',
        ];
    }

    /**
     * Map of value → what choosing it means.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::PER_SQM    => 'Rate × property area',
            self::FIXED_RATE => 'Same amount for every property',
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
     * What choosing this method means, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * Whether dues are charged against the property's area.
     */
    public function isPerSquareMeter(): bool
    {
        return $this->value === self::PER_SQM;
    }
}
