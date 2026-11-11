<?php

namespace App\Services\Amenities;

use App\Enums\BillType;
use App\Models\Amenity\AmenityBooking;
use Illuminate\Support\Facades\DB;

class BookingInvoiceService
{
    // ===== Operations =====

    public function sync(AmenityBooking $booking): void
    {
        $booking->loadMissing(['amenity', 'bill']);

        $bill = $booking->bill;

        if (! $bill
            || $bill->type !== BillType::AMENITY_BOOKING
            || ! $bill->status_enum?->isCollectible()
            || $bill->payments()->exists()) {
            return;
        }

        $lines = $booking->invoiceLines();

        if (empty($lines)) {
            return;
        }

        DB::transaction(function () use ($bill, $booking, $lines) {
            $bill->items()->delete();
            $bill->items()->createMany($lines);
            $bill->update([
                'user_id'        => $booking->user_id,
                'billed_to_name' => $booking->booker_name,
            ]);
            $bill->recalculate();
        });
    }
}
