<?php

namespace App\Queries\Homeowners\Residents;

use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ResidentListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(
        string $search = '',
        string $sortField = 'surname',
        string $sortDirection = 'asc',
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        $query = Resident::query()
            ->select('residents.*')
            ->selectSub(function ($q) {
                $q->selectRaw('COUNT(household_members.id)')
                    ->from('household_members')
                    ->whereIn('household_members.household_id', function ($sq) {
                        $sq->select('own.household_id')->from('household_members as own')
                            ->whereColumn('own.resident_id', 'residents.id');
                    });
            }, 'member_count')
            ->with(['user', 'household.property']);

        return $query->search($search)->orderBy($sortField, $sortDirection)->paginate($perPage);
    }

    public function ids(string $search = ''): array
    {
        return Resident::query()->search($search)->pluck('id')
            ->map(function ($id) {
                return (string) $id;
            })
            ->all();
    }

    public function find(int $id): Resident
    {
        return Resident::with('user')->findOrFail($id);
    }
}
