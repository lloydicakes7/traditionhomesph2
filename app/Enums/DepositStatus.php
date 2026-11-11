<?php

namespace App\Enums;

/**
 * Where the deposit tied to a reservation stands.
 *
 * A booking that requires no deposit stays on NONE for its whole life; one
 * that does is HELD from the moment it is filed — the reservation is what ties
 * the deposit up — and ends either REFUNDED after inspection or FORFEITED.
 *
 * @method static DepositStatus NONE()
 * @method static DepositStatus HELD()
 * @method static DepositStatus REFUNDED()
 * @method static DepositStatus FORFEITED()
 */
class DepositStatus extends Enum
{
    const NONE      = 'none';
    const HELD      = 'held';
    const REFUNDED  = 'refunded';
    const FORFEITED = 'forfeited';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::NONE      => 'No Deposit',
            self::HELD      => 'Held',
            self::REFUNDED  => 'Refunded',
            self::FORFEITED => 'Forfeited',
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
            self::NONE      => 'secondary',
            self::HELD      => 'warning',
            self::REFUNDED  => 'success',
            self::FORFEITED => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the deposit is still in the office's hands, so it can still be
     * returned or kept.
     */
    public function isSettled(): bool
    {
        return in_array($this->value, [self::REFUNDED, self::FORFEITED], true);
    }
}
