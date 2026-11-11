<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Models\User;

/**
 * Amenity bookings, on both sides of the system.
 *
 * The admin side answers "may this officer work with reservations?" — the
 * module permission gates the page, and every move in the booking's life
 * (approving it, recording its payment, closing it, settling its deposit,
 * cancelling it) is an edit of the record, so they all read the same
 * permission rather than inventing abilities nobody can be granted
 * separately. None of them turns on which reservation is in hand, so those
 * methods take the Admin alone and every call site names the class
 * (`@can('approve', AmenityBooking::class)`, the form the panel uses).
 *
 * The portal side answers the only questions the portal can ask: "is this
 * reservation the signed-in homeowner's?", "may they still remove it?" and
 * "may they download its slip?". A homeowner never sees another household's
 * bookings, approves nothing and pays nothing — but a request they filed is
 * theirs to remove while the office has not invoiced it, and its transaction
 * slip is available once the office approves it.
 */
class AmenityBookingPolicy
{
    const MODULE = 'Amenity Bookings';

    /**
     * Determine whether the admin can browse the bookings page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one reservation.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can take a reservation — for a resident or
     * for a guest.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change a reservation.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a reservation.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can approve a submitted reservation.
     */
    public function approve(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can record a payment against a reservation
     * and issue its invoice.
     */
    public function recordPayment(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can close a reservation as used.
     */
    public function complete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can return a reservation's deposit.
     */
    public function refundDeposit(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can keep a reservation's deposit.
     */
    public function forfeitDeposit(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can cancel a reservation.
     */
    public function cancel(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    // =====================================================================
    // Portal side — the homeowner's own reservations
    // =====================================================================

    /**
     * Determine whether this homeowner may read the reservation: their own
     * household's.
     */
    public function viewOwn(User $user, AmenityBooking $booking): bool
    {
        return $booking->user_id !== null && (int) $booking->user_id === (int) $user->id;
    }

    /**
     * Determine whether this homeowner may download the transaction slip: the
     * slip is available once the office has approved the reservation.
     */
    public function downloadSlipOwn(User $user, AmenityBooking $booking): bool
    {
        return $this->viewOwn($user, $booking)
            && (bool) $booking->status_enum?->isApproved();
    }

    /**
     * Determine whether this homeowner may delete the reservation: their own, and
     * one the office has not invoiced yet. A reservation carrying a bill is money
     * on the books, so it is the office that cancels it (with a remark for the
     * homeowner) rather than the portal removing the record underneath it.
     */
    public function deleteOwn(User $user, AmenityBooking $booking): bool
    {
        if ($booking->user_id === null || (int) $booking->user_id !== (int) $user->id) {
            return false;
        }

        return $booking->bill_id === null;
    }
}
