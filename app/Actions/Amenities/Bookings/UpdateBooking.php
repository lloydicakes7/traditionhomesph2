<?php

namespace App\Actions\Amenities\Bookings;

use App\Actions\Amenities\Bookings\Concerns\OpensBooking;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Services\ActivityLogService;
use App\Services\Amenities\BookingInvoiceService;

class UpdateBooking
{
    use OpensBooking;

    private ActivityLogService $activity;

    private BookingInvoiceService $invoices;

    public function __construct(ActivityLogService $activity, BookingInvoiceService $invoices)
    {
        $this->activity = $activity;
        $this->invoices = $invoices;
    }

    // ===== Handle =====

    public function handle(AmenityBooking $booking, array $data, Admin $by): AmenityBooking
    {
        $amenity = Amenity::findOrFail($data['amenity_id']);
        [$startsAt, $endsAt] = $this->windowFrom($data);
        $deposit = $this->depositFrom($data);

        $attributes = [
            'amenity_id'     => $amenity->id,
            'user_id'        => ($data['user_id'] ?? null) ?: null,
            'guest_name'     => ($data['user_id'] ?? null) ? null : (($data['guest_name'] ?? null) ?: null),
            'starts_at'      => $startsAt,
            'ends_at'        => $endsAt,
            'deposit_amount' => $deposit,
            'purpose'        => ($data['purpose'] ?? null) ?: null,
            'remarks'        => ($data['remarks'] ?? null) ?: null,
        ];

        // A deposit already returned or kept keeps its outcome; otherwise the
        // reservation holds the deposit the form now asks for.
        if (! $booking->deposit_status_enum?->isSettled()) {
            $attributes['deposit_status'] = $this->depositStatusFor($deposit);
        }

        $booking->update($attributes);

        $this->invoices->sync($booking->fresh());

        $this->activity->log(
            $by,
            'updated',
            'amenity_booking',
            "Updated the {$amenity->name} booking for {$booking->booker_name}",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $amenity->id]
        );

        return $booking;
    }
}
