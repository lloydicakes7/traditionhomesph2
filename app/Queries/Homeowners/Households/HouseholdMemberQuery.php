<?php

namespace App\Queries\Homeowners\Households;

use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;

class HouseholdMemberQuery
{
    // ===== Rows =====

    public function rows(int $householdId): array
    {
        $household = Household::with('memberRecords.resident.user')->findOrFail($householdId);

        return $household->memberRecords->sortByDesc('is_head')->map(function ($record) {
            return [
                'id'                 => $record->id,
                'resident_id'        => (string) $record->resident_id,
                'resident_name'      => $record->resident?->full_name ?? '—',
                'relationship'       => $record->relationship ?? '',
                'relationship_label' => $record->relationship_label,
                'is_head'            => (bool) $record->is_head,
                'has_account'        => $record->resident?->user !== null,
            ];
        })->values()->all();
    }

    public function selectable(?int $excludeResidentId = null): Collection
    {
        return Resident::whereDoesntHave('householdMemberships')
            ->when($excludeResidentId, function ($q) use ($excludeResidentId) {
                $q->where('id', '!=', $excludeResidentId);
            })
            ->orderBy('surname')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * The household a resident belongs to, and how many other people are on it.
     *
     * A resident who is on no household at all has nothing to add a member to,
     * so null is returned.
     *
     * @return array{household_id:int, other_member_count:int}|null
     */
    public function householdSummaryForResident(int $residentId): ?array
    {
        $resident = Resident::query()
            ->with(['household.memberRecords', 'user.household.memberRecords'])
            ->find($residentId);

        $household = $resident?->household ?: $resident?->user?->household;

        if (! $household) {
            return null;
        }

        $household->loadMissing('memberRecords');

        return [
            'household_id'       => (int) $household->id,
            'other_member_count' => $household->memberRecords
                ->where('resident_id', '!=', $residentId)
                ->count(),
        ];
    }

    // ===== One member =====

    public function residentForm(int $residentId, int $householdId): array
    {
        $resident = Resident::findOrFail($residentId);

        $member = HouseholdMember::where('household_id', $householdId)
            ->where('resident_id', $residentId)
            ->first();

        return [
            'resident' => [
                'surname'                => $resident->surname ?? '',
                'first_name'             => $resident->first_name ?? '',
                'middle_name'            => $resident->middle_name ?? '',
                'suffix'                 => $resident->suffix ?? '',
                'birth_date'             => $resident->birth_date ? $resident->birth_date->format('Y-m-d') : '',
                'birth_place'            => $resident->birth_place ?? '',
                'sex'                    => $resident->sex ?? '',
                'civil_status'           => $resident->civil_status ?? '',
                'citizenship'            => $resident->citizenship ?? '',
                'religion'               => $resident->religion ?? '',
                'occupation'             => $resident->occupation ?? '',
                'mobile_no'              => $resident->mobile_no ?? '',
                'educational_attainment' => $resident->educational_attainment ?? '',
                'spouse_name'            => $resident->spouse_name ?? '',
            ],
            'relationship' => $member?->relationship ?? '',
            'is_head'      => (bool) $member?->is_head,
        ];
    }
}
