<?php

namespace App\Enums;

/**
 * What a complaint is about.
 *
 * The same fixed set on both sides of the system: the homeowner picks one when
 * filing from the portal, the office picks one when recording something
 * reported to it, and the complaints list filters by exactly these — so an
 * officer picks a category rather than inventing one, which is why it is an enum.
 *
 * @method static ComplaintCategory NOISE()
 * @method static ComplaintCategory PARKING()
 * @method static ComplaintCategory MAINTENANCE()
 * @method static ComplaintCategory SECURITY()
 * @method static ComplaintCategory SANITATION()
 * @method static ComplaintCategory NEIGHBOR_DISPUTE()
 * @method static ComplaintCategory AMENITY()
 * @method static ComplaintCategory OTHER()
 */
class ComplaintCategory extends Enum
{
    const NOISE            = 'noise';
    const PARKING          = 'parking';
    const MAINTENANCE      = 'maintenance';
    const SECURITY         = 'security';
    const SANITATION       = 'sanitation';
    const NEIGHBOR_DISPUTE = 'neighbor_dispute';
    const AMENITY          = 'amenity';
    const OTHER            = 'other';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::NOISE            => 'Noise / Disturbance',
            self::PARKING          => 'Parking',
            self::MAINTENANCE      => 'Maintenance / Repair',
            self::SECURITY         => 'Security / Safety',
            self::SANITATION       => 'Sanitation / Garbage',
            self::NEIGHBOR_DISPUTE => 'Neighbor Dispute',
            self::AMENITY          => 'Amenity / Facility',
            self::OTHER            => 'Other',
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
