<?php

namespace App\Actions\Amenities\Bookings;

use App\Enums\DepositStatus;
use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Services\ActivityLogService;

class ForfeitDeposit
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(AmenityBooking $booking, Admin $by): AmenityBooking
    {
        $booking->update([
            'deposit_status'     => DepositStatus::FORFEITED,
            'deposit_settled_at' => now(),
        ]);

        $this->activity->log(
            $by,
            'forfeited',
            'amenity_booking',
            "Forfeited the {$booking->deposit_display} deposit on the {$booking->amenity->name} booking",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $booking->amenity_id]
        );

        return $booking;
    }
}
