<?php

namespace App\Enums;

/**
 * The Household Unit a property is, as Form A asks it.
 *
 * The values are the property types, so the form reads the unit's own record
 * and never a second place that could disagree with it.
 *
 * @method static HouseholdUnit SINGLE_FAMILY()
 * @method static HouseholdUnit TOWNHOUSE()
 * @method static HouseholdUnit CONDOMINIUM()
 * @method static HouseholdUnit DUPLEX()
 * @method static HouseholdUnit MOBILE_HOME()
 */
class HouseholdUnit extends Enum
{
    const SINGLE_FAMILY = PropertyType::HOUSE;
    const TOWNHOUSE     = PropertyType::TOWNHOUSE;
    const CONDOMINIUM   = PropertyType::CONDO;
    const DUPLEX        = PropertyType::DUPLEX;
    const MOBILE_HOME   = PropertyType::MOBILE_HOME;

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return PropertyType::labels();
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * The household unit for a unit's stored type, or null while unset.
     */
    public static function fromPropertyType(?string $type): ?self
    {
        return $type ? self::tryFrom($type) : null;
    }
}
