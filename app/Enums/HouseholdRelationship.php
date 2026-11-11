<?php

namespace App\Enums;

/**
 * A household member's relationship to the family head — the list the RBI
 * form's Part 2 ticks (Father, Mother, Son, …), and the same list the head
 * picks for themselves.
 *
 * The old HouseholdPosition class described the household as a whole. The
 * information belongs to the person, so it is stored on `household_members`
 * and named for what it is: a relationship.
 *
 * @method static HouseholdRelationship FATHER()
 * @method static HouseholdRelationship MOTHER()
 * @method static HouseholdRelationship SON()
 * @method static HouseholdRelationship DAUGHTER()
 * @method static HouseholdRelationship SPOUSE()
 * @method static HouseholdRelationship GRANDFATHER()
 * @method static HouseholdRelationship GRANDMOTHER()
 * @method static HouseholdRelationship FATHER_IN_LAW()
 * @method static HouseholdRelationship MOTHER_IN_LAW()
 * @method static HouseholdRelationship BROTHER_IN_LAW()
 * @method static HouseholdRelationship SISTER_IN_LAW()
 * @method static HouseholdRelationship RELATIVES()
 * @method static HouseholdRelationship OTHERS()
 */
class HouseholdRelationship extends Enum
{
    const FATHER         = 'father';
    const MOTHER         = 'mother';
    const SON            = 'son';
    const DAUGHTER       = 'daughter';
    const SPOUSE         = 'spouse';
    const GRANDFATHER    = 'grandfather';
    const GRANDMOTHER    = 'grandmother';
    const FATHER_IN_LAW  = 'father_in_law';
    const MOTHER_IN_LAW  = 'mother_in_law';
    const BROTHER_IN_LAW = 'brother_in_law';
    const SISTER_IN_LAW  = 'sister_in_law';
    const RELATIVES      = 'relatives';
    const OTHERS         = 'others';

    /**
     * Map of value → human-readable label, in the order the paper form lists
     * them.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::FATHER         => 'Father',
            self::MOTHER         => 'Mother',
            self::SON            => 'Son',
            self::DAUGHTER       => 'Daughter',
            self::SPOUSE         => 'Spouse',
            self::GRANDFATHER    => 'Grandfather',
            self::GRANDMOTHER    => 'Grandmother',
            self::FATHER_IN_LAW  => 'Father-in-law',
            self::MOTHER_IN_LAW  => 'Mother-in-law',
            self::BROTHER_IN_LAW => 'Brother-in-law',
            self::SISTER_IN_LAW  => 'Sister-in-law',
            self::RELATIVES      => 'Relatives',
            self::OTHERS         => 'Others',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    // ===== Helpers =====

    /**
     * Whether this relationship is the free-text "Others" option.
     */
    public function isOther(): bool
    {
        return $this->value === self::OTHERS;
    }

    /**
     * The relationships that place a second generation or a relative beside the
     * head, which the RBI form counts as an extended household.
     *
     * @return array<int, string>
     */
    public static function extendedValues(): array
    {
        return [
            self::GRANDFATHER,
            self::GRANDMOTHER,
            self::FATHER_IN_LAW,
            self::MOTHER_IN_LAW,
            self::BROTHER_IN_LAW,
            self::SISTER_IN_LAW,
            self::RELATIVES,
            self::OTHERS,
        ];
    }
}
