<?php

namespace App\Actions\Amenities\Bookings;

use App\Enums\BookingStatus;
use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Notifications\BookingDecidedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class ApproveBooking
{
    private ActivityLogService $activity;

    private NotificationService $notifications;

    public function __construct(
        ActivityLogService $activity,
        NotificationService $notifications
    ) {
        $this->activity = $activity;
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(AmenityBooking $booking, Admin $by): AmenityBooking
    {
        // Approving holds the window and unlocks the slip. Raising the bill is
        // the office's own next step (CreateBookingInvoice), so the invoice is
        // never written behind the officer's back.
        $booking->update([
            'status'      => BookingStatus::APPROVED,
            'approved_at' => now(),
        ]);

        $this->activity->log(
            $by,
            'approved',
            'amenity_booking',
            "Approved the {$booking->amenity->name} booking for {$booking->booker_name}",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $booking->amenity_id]
        );

        $this->notifications->toHomeowner(
            $booking->user,
            new BookingDecidedNotification($booking, BookingStatus::APPROVED()->label())
        );

        return $booking;
    }
}
