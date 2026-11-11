<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Amenity\AmenityBooking;

/**
 * The office moved one of the homeowner's reservations — approved it,
 * completed it or cancelled it.
 *
 * Sent from the booking actions' own state changes
 * (App\Actions\Amenities\Bookings), so a homeowner who files or cancels their
 * own reservation is not told about it; being told how the office answered it
 * is the point of the notification.
 */
class BookingDecidedNotification extends ThhaNotification
{
    /** @var \App\Models\Amenity\AmenityBooking */
    private $booking;

    /** @var string The status the booking was moved to, as the office reads it. */
    private $status;

    public function __construct(AmenityBooking $booking, string $status)
    {
        $this->booking = $booking;
        $this->status = $status;
    }

    public function type(): NotificationType
    {
        return NotificationType::BOOKING();
    }

    protected function title($notifiable): string
    {
        return 'Booking ' . strtolower($this->status) . ' — ' . optional($this->booking->amenity)->name;
    }

    protected function body($notifiable): string
    {
        return $this->booking->date_time_display
            . ($this->booking->requires_deposit ? ' · Deposit ' . $this->booking->deposit_display : '');
    }

    protected function url($notifiable): ?string
    {
        return route('portal.bookings');
    }
}
