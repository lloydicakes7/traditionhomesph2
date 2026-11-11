<?php

namespace App\Actions\Amenities\Bookings;

use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBookingInvoice
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(AmenityBooking $booking, array $data, Admin $by): Bill
    {
        $bill = $booking->bill;

        if (! $bill) {
            throw ValidationException::withMessages([
                'invoice' => 'This reservation has no invoice to update.',
            ]);
        }

        $bill = DB::transaction(function () use ($booking, $bill, $data) {
            $name = ($data['billed_to_name'] ?? null) ?: $booking->booker_name;

            $bill->update([
                'billed_to_name' => $name,
                'bill_date'      => $data['bill_date'],
                'due_date'       => ($data['due_date'] ?? null) ?: null,
                'discount'       => $data['discount'] ?? 0,
                'notes'          => ($data['notes'] ?? null) ?: null,
            ]);

            $bill->recalculate();

            if ($booking->user_id === null) {
                $booking->update(['guest_name' => $name]);
            }

            return $bill->fresh();
        });

        $this->activity->log(
            $by,
            'updated',
            'amenity_booking',
            "Updated {$bill->reference} for the {$booking->amenity->name} booking",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $booking->amenity_id, 'bill_id' => $bill->id]
        );

        return $bill;
    }
}
