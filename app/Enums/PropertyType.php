<?php

namespace App\Enums;

/**
 * The unit a property is — the "Household Unit" list the RBI form's Part 2
 * ticks, and the type the Properties page and collections rates read.
 *
 * @method static PropertyType HOUSE()
 * @method static PropertyType TOWNHOUSE()
 * @method static PropertyType CONDO()
 * @method static PropertyType DUPLEX()
 * @method static PropertyType MOBILE_HOME()
 */
class PropertyType extends Enum
{
    const HOUSE       = 'house';
    const TOWNHOUSE   = 'townhouse';
    const CONDO       = 'condo';
    const DUPLEX      = 'duplex';
    const MOBILE_HOME = 'mobile_home';

    /**
     * Map of value → human-readable label, in the order the paper form lists
     * them.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::HOUSE       => 'Single-family house',
            self::TOWNHOUSE   => 'Townhouse',
            self::CONDO       => 'Condominium',
            self::DUPLEX      => 'Duplex',
            self::MOBILE_HOME => 'Mobile home',
        ];
    }

    /**
     * Map of value → short abbreviation.
     *
     * @return array<string, string>
     */
    public static function shortLabels(): array
    {
        return [
            self::HOUSE       => 'H',
            self::TOWNHOUSE   => 'TH',
            self::CONDO       => 'Condo',
            self::DUPLEX      => 'D',
            self::MOBILE_HOME => 'MH',
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
     * Short abbreviation for this instance.
     */
    public function shortLabel(): string
    {
        return self::shortLabels()[$this->value] ?? $this->value;
    }

    /**
     * AdminLTE badge theme class for this type.
     */
    public function badgeTheme(): string
    {
        return [
            self::HOUSE       => 'primary',
            self::TOWNHOUSE   => 'info',
            self::CONDO       => 'success',
            self::DUPLEX      => 'secondary',
            self::MOBILE_HOME => 'warning',
        ][$this->value] ?? 'secondary';
    }
}
