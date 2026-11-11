<?php

namespace App\Enums;

/**
 * Where an advance deposit stands: money a homeowner has paid ahead of a bill.
 *
 * The status follows the deposit's balance — a deposit is available while it
 * holds credit, used up when it has none — and it is what a page reads to
 * decide whether a deposit can still be applied.
 *
 * @method static AdvanceDepositStatus AVAILABLE()
 * @method static AdvanceDepositStatus PARTIALLY_USED()
 * @method static AdvanceDepositStatus USED()
 * @method static AdvanceDepositStatus REFUNDED()
 */
class AdvanceDepositStatus extends Enum
{
    const AVAILABLE      = 'available';
    const PARTIALLY_USED = 'partially_used';
    const USED           = 'used';
    const REFUNDED       = 'refunded';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::AVAILABLE      => 'Available',
            self::PARTIALLY_USED => 'Partly Used',
            self::USED           => 'Fully Used',
            self::REFUNDED       => 'Refunded',
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
            self::AVAILABLE      => 'success',
            self::PARTIALLY_USED => 'info',
            self::USED           => 'secondary',
            self::REFUNDED       => 'dark',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the deposit still holds credit that can be applied.
     */
    public function isOpen(): bool
    {
        return in_array($this->value, [self::AVAILABLE, self::PARTIALLY_USED], true);
    }
}
