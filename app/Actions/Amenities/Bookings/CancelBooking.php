<?php

namespace App\Actions\Amenities\Bookings;

use App\Enums\BookingStatus;
use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Notifications\BookingDecidedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class CancelBooking
{
    private ActivityLogService $activity;

    private NotificationService $notifications;

    public function __construct(ActivityLogService $activity, NotificationService $notifications)
    {
        $this->activity = $activity;
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(AmenityBooking $booking, ?string $remarks, Admin $by): AmenityBooking
    {
        $booking->update([
            'status'       => BookingStatus::CANCELLED,
            'cancelled_at' => now(),
            'remarks'      => $remarks ?: $booking->remarks,
        ]);

        $this->activity->log(
            $by,
            'cancelled',
            'amenity_booking',
            "Cancelled the {$booking->amenity->name} booking for {$booking->booker_name}",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $booking->amenity_id]
        );

        $this->notifications->toHomeowner(
            $booking->user,
            new BookingDecidedNotification($booking, BookingStatus::CANCELLED()->label())
        );

        return $booking;
    }
}
