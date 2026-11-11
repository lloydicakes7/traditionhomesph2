<?php

namespace App\Enums;

/**
 * @method static CivilStatus SINGLE()
 * @method static CivilStatus MARRIED()
 * @method static CivilStatus DIVORCED()
 * @method static CivilStatus WIDOWED()
 */
class CivilStatus extends Enum
{
    const SINGLE   = 'single';
    const MARRIED  = 'married';
    const DIVORCED = 'divorced';
    const WIDOWED  = 'widowed';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::SINGLE   => 'Single',
            self::MARRIED  => 'Married',
            self::DIVORCED => 'Divorced',
            self::WIDOWED  => 'Widowed',
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
     * Whether a spouse name belongs to this civil status.
     *
     * The forms show the spouse field only for a married applicant, and the
     * resident payload drops any spouse name a form carried for another status.
     */
    public static function isMarried($value): bool
    {
        return (string) $value === self::MARRIED;
    }

    /**
     * AdminLTE badge theme class for this status.
     */
    public function badgeTheme(): string
    {
        return [
            self::SINGLE   => 'info',
            self::MARRIED  => 'primary',
            self::DIVORCED => 'warning',
            self::WIDOWED  => 'secondary',
        ][$this->value] ?? 'secondary';
    }
}
