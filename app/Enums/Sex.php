<?php

namespace App\Enums;

/**
 * @method static Sex MALE()
 * @method static Sex FEMALE()
 */
class Sex extends Enum
{
    const MALE   = 'male';
    const FEMALE = 'female';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MALE   => 'Male',
            self::FEMALE => 'Female',
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
     * AdminLTE badge theme class for this sex.
     */
    public function badgeTheme(): string
    {
        return [
            self::MALE   => 'primary',
            self::FEMALE => 'info',
        ][$this->value] ?? 'secondary';
    }
}
