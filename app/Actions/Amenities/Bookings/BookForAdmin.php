<?php

namespace App\Actions\Amenities\Bookings;

use App\Actions\Amenities\Bookings\Concerns\OpensBooking;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Services\ActivityLogService;

class BookForAdmin
{
    use OpensBooking;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): AmenityBooking
    {
        $amenity = Amenity::findOrFail($data['amenity_id']);
        [$startsAt, $endsAt] = $this->windowFrom($data);

        $booking = $this->open([
            'amenity_id'          => $amenity->id,
            'user_id'             => ($data['user_id'] ?? null) ?: null,
            'created_by_admin_id' => $by->id,
            'guest_name'          => ($data['user_id'] ?? null) ? null : (($data['guest_name'] ?? null) ?: null),
            'purpose'             => ($data['purpose'] ?? null) ?: null,
            'remarks'             => ($data['remarks'] ?? null) ?: null,
        ], $startsAt, $endsAt, $this->depositFrom($data), true);

        $this->activity->log(
            $by,
            'created',
            'amenity_booking',
            "Booked the {$amenity->name} for {$booking->booker_name}",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $amenity->id]
        );

        return $booking;
    }
}
