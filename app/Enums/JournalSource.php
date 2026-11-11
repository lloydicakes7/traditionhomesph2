<?php

namespace App\Enums;

/**
 * What wrote a journal entry — the module behind the posting.
 *
 * The journal entries list names the source on every row, and the entry's own
 * view shows it beside the date, so an officer reading a posting can always
 * see where it came from: a receipt, an expense, a transfer, cash funding, or
 * the treasurer's own hand. The source is what makes the books auditable — a
 * module posting has a record behind it, and everything else is manual.
 *
 * @method static JournalSource MANUAL()
 * @method static JournalSource PAYMENT()
 * @method static JournalSource EXPENSE()
 * @method static JournalSource TRANSFER()
 * @method static JournalSource FUND_ADDITION()
 */
class JournalSource extends Enum
{
    const MANUAL   = 'manual';
    const PAYMENT  = 'payment';
    const EXPENSE     = 'expense';
    const TRANSFER    = 'transfer';
    const FUND_ADDITION = 'fund_addition';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MANUAL   => 'Manual',
            self::PAYMENT  => 'Payment',
            self::EXPENSE     => 'Expense',
            self::TRANSFER    => 'Transfer',
            self::FUND_ADDITION => 'Cash Funding',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? ucfirst($this->value);
    }

    /**
     * AdminLTE badge theme class for this source.
     */
    public function badgeTheme(): string
    {
        return [
            self::MANUAL   => 'secondary',
            self::PAYMENT  => 'success',
            self::EXPENSE     => 'danger',
            self::TRANSFER    => 'primary',
            self::FUND_ADDITION => 'success',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether a record in another module wrote this entry, so the books can
     * always be traced back to the paper behind them.
     */
    public function isAutomatic(): bool
    {
        return $this->value !== self::MANUAL;
    }
}
