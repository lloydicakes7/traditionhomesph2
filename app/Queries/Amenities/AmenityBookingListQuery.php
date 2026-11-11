<?php

namespace App\Queries\Amenities;

use App\Models\Amenity\AmenityBooking;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AmenityBookingListQuery
{
    const PER_PAGE = 15;

    const SORTABLE = ['starts_at', 'deposit_amount', 'status', 'created_at'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)->with([
            'amenity', 'bill', 'user.household.property', 'user.resident',
        ]);

        $this->order($query, $criteria);

        return $query->paginate($criteria['perPage'] ?? self::PER_PAGE);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function find(int $id): AmenityBooking
    {
        return AmenityBooking::findOrFail($id);
    }

    public function detail(int $id): AmenityBooking
    {
        return AmenityBooking::with([
            'amenity', 'bill', 'user.household.property', 'user.resident', 'createdByAdmin',
        ])->findOrFail($id);
    }

    public function forUser($userId): Collection
    {
        return AmenityBooking::with(['amenity', 'bill'])
            ->forUser($userId)
            ->orderByDesc('starts_at')
            ->get();
    }

    public function forResident($residentId): Collection
    {
        return AmenityBooking::with('amenity')
            ->forResident($residentId)
            ->orderByDesc('starts_at')
            ->get();
    }

    public function findByBill(int $billId): ?AmenityBooking
    {
        return AmenityBooking::with(['amenity', 'bill'])
            ->where('bill_id', $billId)
            ->first();
    }

    public function calendar(?string $month = null): array
    {
        $month = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();
        $month = $month->startOfMonth();

        // Only the reservations holding a window are on the calendar: an approved
        // booking or one already used. A pending request holds nothing yet, and a
        // cancelled one has released its window.
        $bookings = AmenityBooking::with(['amenity', 'user'])
            ->committed()
            ->overlapping($month->copy()->startOfDay(), $month->copy()->endOfMonth()->endOfDay())
            ->orderBy('starts_at')
            ->get()
            ->groupBy(function (AmenityBooking $booking) {
                return $booking->starts_at->format('Y-m-d');
            });

        $cursor = $month->copy()->startOfWeek(Carbon::MONDAY);
        $lastDay = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];

        while ($cursor->lte($lastDay)) {
            $week = [];

            for ($day = 0; $day < 7; $day++) {
                $week[] = $cursor->copy();
                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return [
            'month' => $month,
            'weeks' => $weeks,
            'byDay' => $bookings,
        ];
    }

    // ===== Options =====

    public function owners(): Collection
    {
        return User::query()->with('resident')->orderBy('id')->get();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return AmenityBooking::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null)
            ->forAmenity($filters['amenity'] ?? null)
            ->withDepositStatus($filters['deposit'] ?? null);
    }

    private function order($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? 'starts_at';
        $direction = ($criteria['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($field === 'amenity') {
            $query->leftJoin('amenities', 'amenities.id', '=', 'amenity_bookings.amenity_id')
                ->orderBy('amenities.name', $direction)
                ->select('amenity_bookings.*');

            return;
        }

        $query->orderBy(in_array($field, self::SORTABLE, true) ? $field : 'starts_at', $direction);
    }
}
