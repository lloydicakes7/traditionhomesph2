<?php

namespace App\Actions\Amenities\Bookings;

use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Enums\DepositStatus;
use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBookingInvoice
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(AmenityBooking $booking, array $data, Admin $by): Bill
    {
        if ($booking->bill) {
            return $booking->bill;
        }

        $booking->loadMissing('amenity');

        $lines = $booking->invoiceLines();

        if (empty($lines)) {
            throw ValidationException::withMessages([
                'items' => 'This reservation has nothing to invoice.',
            ]);
        }

        $bill = DB::transaction(function () use ($booking, $data, $lines, $by) {
            $billedTo = ($data['billed_to_name'] ?? null) ?: $booking->booker_name;

            $bill = Bill::create([
                'reference'           => Bill::nextReference(BillType::AMENITY_BOOKING()),
                'user_id'             => $booking->user_id,
                'billed_to_name'      => $billedTo,
                'type'                => BillType::AMENITY_BOOKING,
                'bill_date'           => $data['bill_date'] ?: now()->toDateString(),
                'due_date'            => ($data['due_date'] ?? null) ?: null,
                'discount'            => $data['discount'] ?? 0,
                'notes'               => ($data['notes'] ?? null) ?: 'Amenity reservation ' . $booking->reference,
                'status'              => BillStatus::UNPAID,
                'created_by_admin_id' => $by->id,
            ]);

            $bill->items()->createMany($lines);
            $bill->recalculate();

            $booking->update([
                'bill_id'        => $bill->id,
                'guest_name'     => $booking->user_id === null ? $billedTo : $booking->guest_name,
                'deposit_status' => $this->depositStatus($booking),
            ]);

            $booking->setRelation('bill', $bill);

            return $bill;
        });

        $this->activity->log(
            $by,
            'invoiced',
            'amenity_booking',
            "Raised {$bill->reference} for the {$booking->amenity->name} booking",
            ['amenity_booking_id' => $booking->id, 'amenity_id' => $booking->amenity_id, 'bill_id' => $bill->id]
        );

        return $bill;
    }

    // ===== Helpers =====

    private function depositStatus(AmenityBooking $booking): string
    {
        if ($booking->deposit_status_enum?->isSettled()) {
            return $booking->deposit_status;
        }

        return (float) $booking->deposit_amount > 0 ? DepositStatus::HELD : DepositStatus::NONE;
    }
}
