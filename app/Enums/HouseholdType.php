<?php

namespace App\Enums;

/**
 * @method static HouseholdType OWNER()
 * @method static HouseholdType TENANT()
 * @method static HouseholdType CARETAKER()
 * @method static HouseholdType INFORMAL_SETTLER()
 */
class HouseholdType extends Enum
{
    const OWNER            = 'owner';
    const TENANT           = 'tenant';
    const CARETAKER        = 'caretaker';
    const INFORMAL_SETTLER = 'informal_settler';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::OWNER            => 'Owner',
            self::TENANT           => 'Renter',
            self::CARETAKER        => 'Caretaker',
            self::INFORMAL_SETTLER => 'Informal Settler',
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
     * AdminLTE badge theme class for this type.
     */
    public function badgeTheme(): string
    {
        return [
            self::OWNER            => 'primary',
            self::TENANT           => 'info',
            self::CARETAKER        => 'secondary',
            self::INFORMAL_SETTLER => 'warning',
        ][$this->value] ?? 'secondary';
    }
}
