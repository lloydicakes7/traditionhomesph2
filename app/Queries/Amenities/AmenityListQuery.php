<?php

namespace App\Queries\Amenities;

use App\Models\Amenity\Amenity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AmenityListQuery
{
    const PER_PAGE = 12;

    // ===== Rows =====

    public function paginate(string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Amenity::query()->search($search)->ordered()->paginate($perPage);
    }

    public function find(int $id): Amenity
    {
        return Amenity::findOrFail($id);
    }

    public function detail(int $id): Amenity
    {
        return Amenity::with('schedules')->findOrFail($id);
    }

    public function bookingCount(int $id): int
    {
        return Amenity::findOrFail($id)->bookings()->count();
    }

    public function bookable(): Collection
    {
        return Amenity::query()->bookable()->with('schedules')->ordered()->get();
    }

    // ===== Options =====

    public function options(): array
    {
        return Amenity::query()->ordered()->pluck('name', 'id')->all();
    }
}
