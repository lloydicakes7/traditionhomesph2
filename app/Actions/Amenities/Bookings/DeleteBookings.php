<?php

namespace App\Actions\Amenities\Bookings;

use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteBookings
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $bookings = AmenityBooking::with('amenity')->whereIn('id', $ids)->get();

        foreach ($bookings as $booking) {
            $this->activity->log(
                $by,
                'deleted',
                'amenity_booking',
                "Deleted the {$booking->amenity->name} booking for {$booking->booker_name}",
                ['amenity_booking_id' => $booking->id, 'amenity_id' => $booking->amenity_id]
            );
        }

        return DB::transaction(function () use ($bookings) {
            return AmenityBooking::whereIn('id', $bookings->pluck('id')->all())->delete();
        });
    }
}
