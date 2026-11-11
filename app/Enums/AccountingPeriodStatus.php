<?php

namespace App\Enums;

/**
 * Where an accounting period stands — the month-end and year-end lock.
 *
 * A month with no closed row is open: the books take postings into it like any
 * other. A closed month may not be posted into until it is reopened, which is
 * what makes a set of financial statements mean something — the figures a
 * report reads for a closed month cannot move afterwards. The enum states that
 * rule, so the journal, the transfers and the Close/Reopen
 * controls all read it from one place rather than repeating the list.
 *
 * @method static AccountingPeriodStatus OPEN()
 * @method static AccountingPeriodStatus CLOSED()
 */
class AccountingPeriodStatus extends Enum
{
    const OPEN   = 'open';
    const CLOSED = 'closed';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::OPEN   => 'Open',
            self::CLOSED => 'Closed',
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
            self::OPEN   => 'success',
            self::CLOSED => 'dark',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether a period in this state refuses new postings.
     */
    public function isClosed(): bool
    {
        return $this->value === self::CLOSED;
    }

    /**
     * Every status, as a select's options.
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
