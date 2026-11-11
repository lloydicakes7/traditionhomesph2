<?php

namespace App\Queries\Reports;

use App\Enums\BookingStatus;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Utility\MoneyHelper;
use Illuminate\Support\Collection;

/**
 * What the association's shared facilities are doing: the year's bookings, how
 * they stand, which amenity is booked most, and what each has earned.
 *
 * Each figure is a single grouped read rather than a query per amenity, so the
 * page stays one snapshot however many amenities the association is running —
 * and the per-amenity figures are what let the page draw an amenity as a bubble:
 * bookings across, bookings still ahead up, and what it has earned as the
 * bubble's size.
 *
 * A booking holds no money of its own. What it earned sits on the bill it was
 * settled through, read through the booking's own rate (the charge before the
 * deposit it also carries), which is
 * \App\Models\Amenity\AmenityBooking::rate_amount.
 */
class AmenityReportQuery
{
    /**
     * The amenity snapshot, ready for the page.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $year = (int) now()->format('Y');

        $all = $this->bookingsPerAmenity();
        $thisYear = $this->bookingsPerAmenity(function ($query) use ($year) {
            $query->whereYear('starts_at', $year);
        });
        $upcoming = $this->bookingsPerAmenity(function ($query) {
            $query->where('starts_at', '>=', now());
        });
        $earnings = $this->earningsPerAmenity();

        $byAmenity = [];
        $bubbles = [];

        foreach (Amenity::query()->orderBy('name')->get() as $amenity) {
            $bookingCount = (int) ($all[$amenity->id] ?? 0);
            $upcomingCount = (int) ($upcoming[$amenity->id] ?? 0);
            $earned = (float) ($earnings[$amenity->id] ?? 0);

            $byAmenity[] = [
                'amenity'   => $amenity,
                'bookings'  => $bookingCount,
                'upcoming'  => $upcomingCount,
                'this_year' => (int) ($thisYear[$amenity->id] ?? 0),
                'earned'    => $earned,
                'earnings'  => MoneyHelper::format($earned),
            ];

            // Only a booked amenity belongs on the bubble chart: an amenity with
            // no booking is the empty origin, not a data point.
            if ($bookingCount > 0 || $upcomingCount > 0) {
                $bubbles[] = [
                    'label' => $amenity->name,
                    'x'     => $bookingCount,
                    'y'     => $upcomingCount,
                    'r'     => max($earned, 0),
                    'note'  => MoneyHelper::format($earned) . ' earned',
                ];
            }
        }

        return [
            'year'       => $year,
            'by_status'  => $this->byStatus(),
            'by_amenity' => $byAmenity,
            'bubbles'    => $bubbles,
            'total'      => (int) $all->sum(),
            'this_year'  => (int) $thisYear->sum(),
            'upcoming'   => (int) $upcoming->sum(),
            'earnings'   => MoneyHelper::format($earnings->sum()),
        ];
    }

    /**
     * How many bookings each amenity holds, optionally narrowed to one slice
     * of them — the year's, or the ones still ahead.
     *
     * @return Collection<int|string, int>
     */
    private function bookingsPerAmenity(callable $narrow = null): Collection
    {
        $query = AmenityBooking::query()
            ->selectRaw('amenity_id, COUNT(*) as aggregate')
            ->groupBy('amenity_id');

        if ($narrow) {
            $narrow($query);
        }

        return $query->pluck('aggregate', 'amenity_id');
    }

    /**
     * What each amenity earned, read off its own completed bookings' bills —
     * the amenity was used and the fee earned.
     *
     * @return Collection<int|string, float>
     */
    private function earningsPerAmenity(): Collection
    {
        return AmenityBooking::query()
            ->where('status', BookingStatus::COMPLETED)
            ->with('bill.items')
            ->get()
            ->groupBy('amenity_id')
            ->map(function ($bookings) {
                return round($bookings->sum('rate_amount'), 2);
            });
    }

    /**
     * How the bookings stand today, status by status.
     *
     * @return array<int, array<string, mixed>>
     */
    private function byStatus(): array
    {
        $byStatus = [];

        foreach (BookingStatus::cases() as $status) {
            $byStatus[$status->value] = [
                'label' => $status->label(),
                'count' => AmenityBooking::query()->where('status', $status->value)->count(),
            ];
        }

        return $byStatus;
    }
}
