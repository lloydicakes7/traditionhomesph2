<?php

namespace App\Enums;

/**
 * What raised a bill.
 *
 * Every bill in the collections ledger comes from one of three places: the
 * recurring monthly dues, a one-off invoice raised by the office, or an amenity
 * reservation. The type is not just a label — it drives the reference the bill
 * is filed under, which screen lists it, and which income account its posting
 * credits — so the rule is stated once, here.
 *
 * @method static BillType MONTHLY_DUE()
 * @method static BillType INVOICE()
 * @method static BillType AMENITY_BOOKING()
 */
class BillType extends Enum
{
    const MONTHLY_DUE      = 'monthly_due';
    const INVOICE          = 'invoice';
    const AMENITY_BOOKING  = 'amenity_booking';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MONTHLY_DUE     => 'Monthly Due',
            self::INVOICE         => 'Invoice',
            self::AMENITY_BOOKING => 'Amenity Booking',
        ];
    }

    /**
     * Map of value → what the type means, for the record's own page.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::MONTHLY_DUE     => 'The recurring association dues for one month.',
            self::INVOICE         => 'A one-off charge raised by the office.',
            self::AMENITY_BOOKING => 'A charge for the use of a shared facility.',
        ];
    }

    /**
     * Map of value → the prefix its reference is filed under.
     *
     * @return array<string, string>
     */
    public static function referencePrefixes(): array
    {
        return [
            self::MONTHLY_DUE     => 'DUE-',
            self::INVOICE         => 'INV-',
            self::AMENITY_BOOKING => 'AMN-',
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
     * What this type means, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * The prefix this type's references carry — `INV-`, `DUE-`, `AMN-`.
     */
    public function referencePrefix(): string
    {
        return self::referencePrefixes()[$this->value] ?? '';
    }

    /**
     * AdminLTE badge theme class for this type.
     */
    public function badgeTheme(): string
    {
        return [
            self::MONTHLY_DUE     => 'primary',
            self::INVOICE         => 'info',
            self::AMENITY_BOOKING => 'success',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether this type is the recurring monthly dues, which carry a period.
     */
    public function isMonthlyDue(): bool
    {
        return $this->value === self::MONTHLY_DUE;
    }

    /**
     * Whether this type is a charge for using one of the shared facilities,
     * which earns to the amenities income account.
     */
    public function isAmenityBooking(): bool
    {
        return $this->value === self::AMENITY_BOOKING;
    }
}
