<?php

namespace App\Enums;

/**
 * What kind of account a chart-of-accounts line is.
 *
 * The five types are the accounting equation itself — assets and liabilities
 * and equity on one side, income and expenses on the other — and the type
 * decides two things nothing else may re-derive: which side of the books the
 * account normally sits on (`normalSide()`), and which badge the chart of
 * accounts and every report paints beside it.
 *
 * The stored value for the income type stays `revenue` (a bookkeeping term);
 * only what the office reads is called *Income*, which is what the HOA
 * actually works for.
 *
 * @method static AccountType ASSET()
 * @method static AccountType LIABILITY()
 * @method static AccountType EQUITY()
 * @method static AccountType INCOME()
 * @method static AccountType EXPENSE()
 */
class AccountType extends Enum
{
    const ASSET     = 'asset';
    const LIABILITY = 'liability';
    const EQUITY    = 'equity';
    const INCOME   = 'income';
    const EXPENSE   = 'expense';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ASSET     => 'Asset',
            self::LIABILITY => 'Liability',
            self::EQUITY    => 'Equity',
            self::INCOME   => 'Income',
            self::EXPENSE   => 'Expense',
        ];
    }

    /**
     * Map of value → the type's heading in the chart and the statements.
     *
     * @return array<string, string>
     */
    public static function headings(): array
    {
        return [
            self::ASSET     => 'ASSETS',
            self::LIABILITY => 'LIABILITIES',
            self::EQUITY    => 'EQUITY',
            self::INCOME   => 'INCOME',
            self::EXPENSE   => 'EXPENSES',
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
     * The type's heading, as the chart and the statements write it.
     */
    public function heading(): string
    {
        return self::headings()[$this->value] ?? strtoupper($this->value);
    }

    /**
     * AdminLTE badge theme class for this type.
     */
    public function badgeTheme(): string
    {
        return [
            self::ASSET     => 'info',
            self::LIABILITY => 'warning',
            self::EQUITY    => 'primary',
            self::INCOME    => 'success',
            self::EXPENSE   => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * The side this type of account normally carries its balance on.
     *
     */
    public function normalSide(): string
    {
        return in_array($this->value, [self::ASSET, self::EXPENSE], true)
            ? 'debit'
            : 'credit';
    }

    /**
     * Whether this type of account normally carries a debit balance.
     */
    public function isDebitNormal(): bool
    {
        return $this->normalSide() === 'debit';
    }

    /**
     * Every type, as a select's options.
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
