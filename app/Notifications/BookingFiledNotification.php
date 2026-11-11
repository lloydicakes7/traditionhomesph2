<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Amenity\AmenityBooking;

/**
 * A reservation was filed from the portal — something for the operations desk
 * to confirm.
 *
 * It goes only to the officers whose role grants Amenity Bookings, so a booking
 * the office itself wrote for a homeowner is never reported to an officer whose
 * page it would not open (see NotificationService::toOfficers).
 */
class BookingFiledNotification extends OfficerNotification
{
    /** @var \App\Models\Amenity\AmenityBooking */
    private $booking;

    public function __construct(AmenityBooking $booking)
    {
        $this->booking = $booking;
    }

    public function type(): NotificationType
    {
        return NotificationType::BOOKING();
    }

    public function governedBy(): array
    {
        return ['viewAny', AmenityBooking::class];
    }

    protected function title($notifiable): string
    {
        return 'Booking filed — ' . optional($this->booking->amenity)->name;
    }

    protected function body($notifiable): string
    {
        return $this->booking->booker_name . ' · ' . $this->booking->date_time_display
            . ' · ' . $this->booking->amount_display;
    }

    protected function url($notifiable): ?string
    {
        return route('amenities.bookings');
    }
}
