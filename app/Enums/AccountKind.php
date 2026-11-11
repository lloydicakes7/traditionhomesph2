<?php

namespace App\Enums;

/**
 * The three kinds of account an officer works with.
 *
 * The books still store an accounting type — cash accounts are assets, and
 * income and expenses keep their own type — but the office only ever chooses
 * one of these. The kind decides the type, whether the account holds money,
 * and which block of codes the next account is numbered in.
 *
 * @method static AccountKind MONEY()
 * @method static AccountKind INCOME()
 * @method static AccountKind EXPENSE()
 */
class AccountKind extends Enum
{
    const MONEY   = 'money';
    const INCOME  = 'income';
    const EXPENSE = 'expense';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MONEY   => 'Cash',
            self::INCOME  => 'Income',
            self::EXPENSE => 'Expense',
        ];
    }

    /**
     * Map of value → the chart's group heading.
     *
     * @return array<string, string>
     */
    public static function headings(): array
    {
        return [
            self::INCOME  => 'Income',
            self::EXPENSE => 'Expenses',
            self::MONEY   => 'Cash',
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
     * The kind's heading, as the chart groups it.
     */
    public function heading(): string
    {
        return self::headings()[$this->value] ?? strtoupper($this->value);
    }

    /**
     * The stored accounting type this kind is posted under.
     */
    public function type(): AccountType
    {
        switch ($this->value) {
            case self::INCOME:
                return AccountType::INCOME();
            case self::EXPENSE:
                return AccountType::EXPENSE();
            default:
                return AccountType::ASSET();
        }
    }

    /**
     * Whether accounts of this kind are places money sits.
     */
    public function isCash(): bool
    {
        return $this->value === self::MONEY;
    }

    /**
     * The first code in this kind's block.
     */
    public function baseCode(): int
    {
        switch ($this->value) {
            case self::INCOME:
                return 4000;
            case self::EXPENSE:
                return 5000;
            default:
                return 1000;
        }
    }

    /**
     * AdminLTE badge theme class for this kind.
     */
    public function badgeTheme(): string
    {
        return [
            self::MONEY   => 'info',
            self::INCOME  => 'success',
            self::EXPENSE => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Every kind, as a select's options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach ([self::INCOME(), self::EXPENSE(), self::MONEY()] as $kind) {
            $options[$kind->value] = $kind->label();
        }

        return $options;
    }
}
