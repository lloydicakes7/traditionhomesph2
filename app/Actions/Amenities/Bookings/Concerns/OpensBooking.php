<?php

namespace App\Actions\Amenities\Bookings\Concerns;

use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Models\Amenity\AmenityBooking;
use Illuminate\Support\Carbon;

trait OpensBooking
{
    // ===== Window =====

    private function windowFrom(array $data): array
    {
        $date = Carbon::parse($data['date']);

        return [
            $date->copy()->setTimeFromTimeString($data['start_time']),
            $date->copy()->setTimeFromTimeString($data['end_time']),
        ];
    }

    // ===== Deposit =====

    private function depositFrom(array $data): float
    {
        return round(max(0, (float) ($data['deposit_amount'] ?? 0)), 2);
    }

    private function depositStatusFor(float $deposit): string
    {
        return $deposit > 0 ? DepositStatus::HELD : DepositStatus::NONE;
    }

    // ===== Booking =====

    private function open(
        array $attributes,
        Carbon $startsAt,
        Carbon $endsAt,
        float $deposit,
        bool $approved
    ): AmenityBooking {
        return AmenityBooking::create($attributes + [
            'starts_at'      => $startsAt,
            'ends_at'        => $endsAt,
            'deposit_amount' => $deposit,
            'deposit_status' => $this->depositStatusFor($deposit),
            'status'         => $approved ? BookingStatus::APPROVED : BookingStatus::PENDING,
            'approved_at'    => $approved ? now() : null,
        ]);
    }
}
