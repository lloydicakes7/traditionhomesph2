<?php

namespace App\Enums;

/**
 * Where a bill stands, from the day it is raised to the day it is settled.
 *
 * Every bill shares this enum — a one-off invoice, a month's due and an amenity
 * reservation carry the same states — and it also owns what each state means,
 * so no page re-derives "is this still collectible?" from a list of statuses.
 *
 * @method static BillStatus UNPAID()
 * @method static BillStatus PARTIAL()
 * @method static BillStatus PAID()
 * @method static BillStatus OVERDUE()
 * @method static BillStatus CANCELLED()
 */
class BillStatus extends Enum
{
    const UNPAID    = 'unpaid';
    const PARTIAL   = 'partial';
    const PAID      = 'paid';
    const OVERDUE   = 'overdue';
    const CANCELLED = 'cancelled';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::UNPAID    => 'Unpaid',
            self::PARTIAL   => 'Partial',
            self::PAID      => 'Paid',
            self::OVERDUE   => 'Overdue',
            self::CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Map of value → what the status means, for the record's own page.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::UNPAID    => 'Raised and waiting for payment.',
            self::PARTIAL   => 'Partly paid — a balance is still outstanding.',
            self::PAID      => 'Settled in full.',
            self::OVERDUE   => 'Past its due date with a balance still outstanding.',
            self::CANCELLED => 'Withdrawn — nothing is collectible on it.',
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
     * What this status means, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * AdminLTE badge theme class for this status.
     */
    public function badgeTheme(): string
    {
        return [
            self::UNPAID    => 'warning',
            self::PARTIAL   => 'info',
            self::PAID      => 'success',
            self::OVERDUE   => 'danger',
            self::CANCELLED => 'secondary',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the bill is closed: nothing more is owed on it.
     */
    public function isSettled(): bool
    {
        return in_array($this->value, [self::PAID, self::CANCELLED], true);
    }

    /**
     * Whether money can still be collected against it.
     */
    public function isCollectible(): bool
    {
        return ! $this->isSettled();
    }
}
