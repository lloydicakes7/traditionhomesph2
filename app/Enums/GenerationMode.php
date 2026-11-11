<?php

namespace App\Enums;

/**
 * Whether monthly dues invoices are raised by the office or by the system.
 *
 * The mode says who starts a month's billing: the office, from the Monthly
 * Dues Generator, or the scheduler on the configured generation day. The
 * generator reads it to explain what the button it shows will actually do.
 *
 * @method static GenerationMode MANUAL()
 * @method static GenerationMode AUTOMATIC()
 */
class GenerationMode extends Enum
{
    const MANUAL    = 'manual';
    const AUTOMATIC = 'automatic';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MANUAL    => 'Manual',
            self::AUTOMATIC => 'Automatic',
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
            self::MANUAL    => 'Manually generate a bill.',
            self::AUTOMATIC => 'The system automatically raise a bill.',
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
     * What choosing this mode means, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * Whether the system raises the invoices itself.
     */
    public function isAutomatic(): bool
    {
        return $this->value === self::AUTOMATIC;
    }
}
