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

        $this->search($query, $search);

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage);
    }

    public function ids(string $search = ''): array
    {
        $query = Resident::query();

        $this->search($query, $search);

        return $query->pluck('id')
            ->map(function ($id) {
                return (string) $id;
            })
            ->all();
    }

    public function find(int $id): Resident
    {
        return Resident::with('user')->findOrFail($id);
    }

    // ===== Helpers =====

    private function search($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('surname', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('mobile_no', 'like', "%{$search}%")
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('email', 'like', "%{$search}%");
                });
        });
    }
}
