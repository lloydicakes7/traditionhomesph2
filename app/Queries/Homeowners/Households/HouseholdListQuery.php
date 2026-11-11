<?php

namespace App\Queries\Homeowners\Households;

use App\Enums\HouseholdType;
use App\Models\Household\Household;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HouseholdListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(
        string $search = '',
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        return Household::with([
            'property.address',
            'headMember.resident.user',
            'memberRecords.resident.user',
        ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($wq) use ($search) {
                    $wq->whereHas('memberRecords.resident', function ($pq) use ($search) {
                        $pq->where('surname', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%");
                    })->orWhereHas('property', function ($prq) use ($search) {
                        $prq->where('block', 'like', "%{$search}%")
                            ->orWhere('lot', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function find(int $id): Household
    {
        return Household::with(['headMember.resident', 'memberRecords.resident'])->findOrFail($id);
    }

    // ===== One household =====

    public function view(int $id): array
    {
        $household = Household::with([
            'property.address',
            'headMember.resident.user',
            'memberRecords.resident.user',
        ])->findOrFail($id);

        $head = $household->head_resident;

        $data = [
            'id'               => $household->id,
            'name'             => $household->rbi_name,
            'homeowner'        => $head?->full_name ?? '—',
            'homeowner_id'     => $head?->id,
            'homeowner_photo'  => $head?->photo_url,
            'head_relationship' => $household->head_relationship_label,
            'property'         => $household->property?->block_lot_label ?? '—',
            'property_address' => $household->property?->address?->full_address ?? '—',
            'type'             => HouseholdType::labels()[$household->type] ?? $household->type,
            'type_badge'       => $household->type_enum?->badgeTheme() ?? 'secondary',
            'families_count'   => (int) ($household->families_count ?: 1),
            'tenure'           => $household->tenure_label,
            'prev_address'     => $household->prev_address,
            'created_at'       => $household->created_at?->format('M d, Y'),
            'members'          => [],
        ];

        foreach ($household->memberRecords as $record) {
            $data['members'][] = [
                'id'            => $record->id,
                'full_name'     => $record->resident?->full_name ?? '—',
                'relationship'  => $record->relationship_label,
                'age'           => $record->resident?->birth_date ? $record->resident->birth_date->age : null,
                'resident_id'   => $record->resident_id,
                'is_head'       => (bool) $record->is_head,
                'has_account'   => $record->resident?->user !== null,
            ];
        }

        return $data;
    }

    public function forUser(int $userId): ?Household
    {
        $residentId = User::whereKey($userId)->value('resident_id');

        if ($residentId === null) {
            return null;
        }

        return Household::whereHas('memberRecords', function ($query) use ($residentId) {
            $query->where('resident_id', $residentId);
        })->first();
    }

    public function idsForProperty(int $propertyId): array
    {
        return Household::where('property_id', $propertyId)->pluck('id')->all();
    }
}
