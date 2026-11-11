<?php

namespace App\Actions\Amenities\Bookings;

use App\Models\Amenity\AmenityBooking;

/**
 * Remove a reservation the homeowner filed from the portal.
 *
 * Cancelling a reservation is the office's move — it keeps the record and
 * answers the homeowner with a remark. A homeowner who no longer wants the
 * facility removes their own request instead, and only while the office has not
 * invoiced it (AmenityBookingPolicy::deleteOwn).
 */
class DeleteOwnBooking
{
    // ===== Handle =====

    public function handle(AmenityBooking $booking): void
    {
        $booking->delete();
    }
}
