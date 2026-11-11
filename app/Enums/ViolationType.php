<?php

namespace App\Enums;

/**
 * What a homeowner was cited for.
 *
 * A fixed set the panel's own rules are written against — the violation form
 * offers exactly these, the list filters by exactly these, and the dashboards
 * count by them — which is why it is an enum rather than an office-managed
 * lookup table: an officer picks a type, they do not add one.
 *
 * @method static ViolationType EXCESSIVE_NOISE()
 * @method static ViolationType IMPROPER_PARKING()
 * @method static ViolationType UNAUTHORIZED_CONSTRUCTION()
 * @method static ViolationType PET_VIOLATION()
 * @method static ViolationType WASTE_DISPOSAL()
 * @method static ViolationType PROPERTY_UPKEEP()
 * @method static ViolationType CURFEW_VIOLATION()
 * @method static ViolationType OTHER()
 */
class ViolationType extends Enum
{
    const EXCESSIVE_NOISE         = 'excessive_noise';
    const IMPROPER_PARKING        = 'improper_parking';
    const UNAUTHORIZED_CONSTRUCTION = 'unauthorized_construction';
    const PET_VIOLATION           = 'pet_violation';
    const WASTE_DISPOSAL          = 'waste_disposal';
    const PROPERTY_UPKEEP         = 'property_upkeep';
    const CURFEW_VIOLATION        = 'curfew_violation';
    const OTHER                   = 'other';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::EXCESSIVE_NOISE           => 'Excessive Noise',
            self::IMPROPER_PARKING          => 'Improper Parking',
            self::UNAUTHORIZED_CONSTRUCTION => 'Unauthorized Construction',
            self::PET_VIOLATION             => 'Pet / Animal Violation',
            self::WASTE_DISPOSAL            => 'Improper Waste Disposal',
            self::PROPERTY_UPKEEP           => 'Property Upkeep',
            self::CURFEW_VIOLATION          => 'Curfew Violation',
            self::OTHER                     => 'Other',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }
}
