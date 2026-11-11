<?php

namespace App\Actions\Amenities\Bookings;

use App\Actions\Amenities\Bookings\Concerns\OpensBooking;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\User;
use App\Notifications\BookingFiledNotification;
use App\Services\NotificationService;

class RequestBooking
{
    use OpensBooking;

    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(Amenity $amenity, array $data, User $user): AmenityBooking
    {
        [$startsAt, $endsAt] = $this->windowFrom($data);

        // Every reservation waits for the office to approve it. The deposit is
        // set by the office when it takes the reservation, so a portal request
        // starts without one.
        $booking = $this->open([
            'amenity_id' => $amenity->id,
            'user_id'    => $user->id,
            'guest_name' => null,
            'purpose'    => ($data['purpose'] ?? null) ?: null,
        ], $startsAt, $endsAt, 0, false);

        $this->notifications->toOfficers(new BookingFiledNotification($booking));

        return $booking;
    }
}
