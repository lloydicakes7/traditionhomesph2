<?php

namespace App\Queries\Homeowners\Properties;

use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertyListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(
        string $search = '',
        string $filterType = '',
        string $filterOccupancy = '',
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        $query = Property::with(['address', 'currentHousehold' => function ($q) {
            $q->with('user.resident');
        }]);

        $this->filter($query, $search, $filterType, $filterOccupancy);

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage);
    }

    public function ids(string $search = '', string $filterType = '', string $filterOccupancy = ''): array
    {
        $query = Property::query();

        $this->filter($query, $search, $filterType, $filterOccupancy);

        return $query->pluck('id')
            ->map(function ($id) {
                return (string) $id;
            })
            ->all();
    }

    public function find(int $id): Property
    {
        return Property::with([
            'address',
            'currentHousehold' => function ($q) {
                $q->with('user.resident', 'memberRecords.resident');
            },
        ])->findOrFail($id);
    }

    // ===== Helpers =====

    private function filter($query, string $search, string $filterType, string $filterOccupancy): void
    {
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('block', 'like', "%{$search}%")
                    ->orWhere('lot', 'like', "%{$search}%")
                    ->orWhereHas('address', function ($aq) use ($search) {
                        $aq->where('subdivision', 'like', "%{$search}%")
                            ->orWhere('barangay', 'like', "%{$search}%");
                    });
            });
        }

        if ($filterType !== '') {
            $query->where('type', $filterType);
        }

        if ($filterOccupancy === 'has_owner') {
            $query->whereHas('currentHousehold');
        } elseif ($filterOccupancy === 'vacant') {
            $query->whereDoesntHave('currentHousehold');
        }
    }
}
