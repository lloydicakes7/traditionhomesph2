<?php

namespace App\Enums;

/**
 * Why a journal entry was written.
 *
 * Almost every posting is standard — the system wrote it from a payment, an
 * expense or a transfer. The rest are the reasons the treasurer opens the New
 * Journal Entry dialog: opening the books, adjusting an account into line,
 * correcting a figure, closing a period, or reversing an earlier posting.
 *
 * @method static JournalClassification STANDARD()
 * @method static JournalClassification ADJUSTING()
 * @method static JournalClassification CORRECTING()
 * @method static JournalClassification CLOSING()
 * @method static JournalClassification REVERSING()
 * @method static JournalClassification OPENING()
 */
class JournalClassification extends Enum
{
    const STANDARD   = 'standard';
    const ADJUSTING  = 'adjusting';
    const CORRECTING = 'correcting';
    const CLOSING    = 'closing';
    const REVERSING  = 'reversing';
    const OPENING    = 'opening';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::STANDARD   => 'Standard',
            self::ADJUSTING  => 'Adjusting',
            self::CORRECTING => 'Correcting',
            self::CLOSING    => 'Closing',
            self::REVERSING  => 'Reversing',
            self::OPENING    => 'Opening',
        ];
    }

    /**
     * Map of value → the plain-language hint shown under the classification.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::STANDARD   => 'An everyday posting.',
            self::ADJUSTING  => 'An adjustment that brings an account into line.',
            self::CORRECTING => 'A correction of a figure already posted.',
            self::CLOSING    => 'A period-end entry that closes the books.',
            self::REVERSING  => 'A posting that reverses an earlier entry.',
            self::OPENING    => 'The entry that opens a new set of books.',
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
     * What this classification is for, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * AdminLTE badge theme class for this classification.
     */
    public function badgeTheme(): string
    {
        return [
            self::STANDARD   => 'secondary',
            self::ADJUSTING  => 'info',
            self::CORRECTING => 'primary',
            self::CLOSING    => 'dark',
            self::REVERSING  => 'warning',
            self::OPENING    => 'success',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Every classification, as a select's options.
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
