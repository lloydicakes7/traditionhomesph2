<?php

namespace App\Queries\Amenities;

use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use Illuminate\Support\Carbon;

/**
 * The rules a reservation is offered under, as the booking forms read them.
 *
 * What a facility costs, whether it is open at the time asked for and whether
 * that time is already taken are questions about a record, so the form asks
 * them here rather than keeping its own copy of the rule. The actions that
 * store a booking read the same amenity methods it reads.
 */
class BookingWindowQuery
{
    // ===== Window =====

    /**
     * The chosen date and clock times as the two moments a booking is kept in.
     *
     * @return array<int, Carbon>
     */
    public function windowFrom(array $data): array
    {
        $date = Carbon::parse($data['date']);

        return [
            $date->copy()->setTimeFromTimeString($data['start_time']),
            $date->copy()->setTimeFromTimeString($data['end_time']),
        ];
    }

    /**
     * Why the requested window cannot be booked, or null when it can.
     */
    public function windowError(Amenity $amenity, $startsAt, $endsAt, ?int $ignoreBookingId = null): ?string
    {
        $startsAt = Carbon::parse($startsAt);
        $endsAt = Carbon::parse($endsAt);

        if ($endsAt->lte($startsAt)) {
            return 'End time must be after the start time.';
        }

        if (! $startsAt->isSameDay($endsAt)) {
            return 'Start and end must be on the same day.';
        }

        if (! $amenity->isOpenDuring($startsAt, $endsAt)) {
            return $amenity->has_schedule
                ? 'Not open at that time — open ' . $amenity->schedule_summary . '.'
                : 'This facility has no opening hours set yet.';
        }

        if (! $this->isAvailable($amenity, $startsAt, $endsAt, $ignoreBookingId)) {
            return 'That time is already booked.';
        }

        return null;
    }

    // ===== Price =====

    /**
     * What the reservation will cost and what it asks for back, for the preview
     * shown while the form is being filled in. The rate is the facility's own
     * rule; the deposit is the amount the officer entered on the reservation.
     *
     * @return array{amount: float, deposit: float}|null
     */
    public function preview(array $data): ?array
    {
        if (empty($data['amenity_id']) || empty($data['date'])
            || empty($data['start_time']) || empty($data['end_time'])) {
            return null;
        }

        $amenity = Amenity::with('schedules')->find($data['amenity_id']);

        if (! $amenity) {
            return null;
        }

        [$startsAt, $endsAt] = $this->windowFrom($data);

        return [
            'amount'  => $amenity->rateFor($startsAt, $endsAt),
            'deposit' => round(max(0, (float) ($data['deposit_amount'] ?? 0)), 2),
        ];
    }

    // ===== Helpers =====

    private function isAvailable(Amenity $amenity, $startsAt, $endsAt, ?int $ignoreBookingId = null): bool
    {
        return ! AmenityBooking::query()
            ->where('amenity_id', $amenity->id)
            ->when($ignoreBookingId, function ($query) use ($ignoreBookingId) {
                $query->where('id', '!=', $ignoreBookingId);
            })
            ->committed()
            ->overlapping($startsAt, $endsAt)
            ->exists();
    }
}
