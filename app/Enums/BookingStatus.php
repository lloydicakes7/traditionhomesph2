<?php

namespace App\Enums;

/**
 * Where a reservation stands, from the homeowner's request to the day the
 * amenity has been used and the file closed.
 *
 * A booking is filed PENDING and waits for the office to approve it. Once it is
 * APPROVED the transaction slip becomes available and the homeowner brings it
 * to the HOA office to pay and use the facility; the office then closes it as
 * COMPLETED. A CANCELLED booking was withdrawn or refused, and is closed.
 *
 * The enum also owns which moves each status allows, so no page has to
 * re-derive "can this still be approved?" from a list of statuses. Payment is
 * not a status of its own: it is a fact about the reservation's bill, so an
 * approved booking stays approved while its bill is settled.
 *
 * @method static BookingStatus PENDING()
 * @method static BookingStatus APPROVED()
 * @method static BookingStatus COMPLETED()
 * @method static BookingStatus CANCELLED()
 */
class BookingStatus extends Enum
{
    const PENDING   = 'pending';
    const APPROVED  = 'approved';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING   => 'Pending',
            self::APPROVED  => 'Approved',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Map of value → what the status means, for the booking timeline.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::PENDING   => 'Submitted and waiting for the office to approve it.',
            self::APPROVED  => 'Approved — download the transaction slip and bring it to the HOA office to pay and use the facility.',
            self::COMPLETED => 'Completed — the amenity has been used and the booking closed.',
            self::CANCELLED => 'Cancelled — the reservation was withdrawn or refused.',
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
     * What this status means, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * AdminLTE badge theme class for this status.
     */
    public function badgeTheme(): string
    {
        return [
            self::PENDING   => 'warning',
            self::APPROVED  => 'info',
            self::COMPLETED => 'primary',
            self::CANCELLED => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the booking is closed: nothing else can happen to it.
     */
    public function isFinal(): bool
    {
        return in_array($this->value, [self::COMPLETED, self::CANCELLED], true);
    }

    /**
     * Whether the booking was approved — the state that unlocks its transaction
     * slip.
     */
    public function isApproved(): bool
    {
        return $this->value === self::APPROVED;
    }

    /**
     * Whether the office can still approve the booking.
     */
    public function canBeApproved(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Whether a payment can be recorded against the booking.
     */
    public function canBePaid(): bool
    {
        return $this->value === self::APPROVED;
    }

    /**
     * Whether the booking can be marked as used and closed.
     */
    public function canBeCompleted(): bool
    {
        return $this->value === self::APPROVED;
    }

    /**
     * Whether the booking can still be cancelled — a pending or approved
     * reservation can be, a completed or already cancelled one cannot.
     */
    public function canBeCancelled(): bool
    {
        return ! $this->isFinal();
    }

    // ===== Helpers =====

    /**
     * The statuses that hold a window — the approved and completed reservations
     * the calendar shows. A pending request holds nothing, and a cancelled one
     * has released its window.
     *
     * @return array<int, string>
     */
    public static function committedValues(): array
    {
        return [self::APPROVED, self::COMPLETED];
    }
}
