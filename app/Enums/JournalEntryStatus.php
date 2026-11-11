<?php

namespace App\Enums;

/**
 * Where a journal entry stands in the books.
 *
 * An entry posted by a module is posted the moment it is written — the books
 * follow the records, not a review step. A treasurer's own entry may be saved
 * as a draft first. A module's posting is voided when its source is withdrawn,
 * so the books keep the trail; a manual entry may be removed outright. Only a
 * posted entry counts towards any balance, which is the one rule every report
 * leans on.
 *
 * @method static JournalEntryStatus DRAFT()
 * @method static JournalEntryStatus POSTED()
 * @method static JournalEntryStatus VOID()
 */
class JournalEntryStatus extends Enum
{
    const DRAFT  = 'draft';
    const POSTED = 'posted';
    const VOID   = 'void';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DRAFT  => 'Draft',
            self::POSTED => 'Posted',
            self::VOID   => 'Void',
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
            self::DRAFT  => 'secondary',
            self::POSTED => 'success',
            self::VOID   => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether an entry in this state counts towards the books' balances.
     */
    public function affectsBalances(): bool
    {
        return $this->value === self::POSTED;
    }

    /**
     * Whether an entry in this state may still be changed or posted.
     */
    public function isEditable(): bool
    {
        return $this->value === self::DRAFT;
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
