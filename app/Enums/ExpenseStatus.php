<?php

namespace App\Enums;

/**
 * Where an expense stands: logged as a draft until the office approves it.
 *
 * Approval is the only move. It locks the record and leaves it for accounting
 * to post; a draft is the only expense that may be edited, and deleting is how
 * either state is discarded.
 *
 * @method static ExpenseStatus DRAFT()
 * @method static ExpenseStatus APPROVED()
 */
class ExpenseStatus extends Enum
{
    const DRAFT    = 'draft';
    const APPROVED = 'approved';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DRAFT    => 'Draft',
            self::APPROVED => 'Approved',
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
            self::DRAFT    => 'Logged and waiting to be approved.',
            self::APPROVED => 'Approved and waiting for accounting to post it.',
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
            self::DRAFT    => 'warning',
            self::APPROVED => 'success',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the expense may be edited — only a draft can be corrected.
     */
    public function isEditable(): bool
    {
        return $this->value === self::DRAFT;
    }
}
