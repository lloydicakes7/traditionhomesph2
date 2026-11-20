<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Resident;
use App\Services\ActivityLogService;

class AddHouseholdMember
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(
        int $householdId,
        int $residentId,
        ?string $relationship,
        bool $isHead = false,
        ?Admin $by = null
    ): HouseholdMember {
        $household = Household::findOrFail($householdId);

        if ($isHead) {
            $resident = Resident::find($residentId);

            if ($resident === null || ! $resident->canHeadHousehold()) {
                throw new \RuntimeException('A household head must be at least '.Resident::MINIMUM_HEAD_AGE.' years old.');
            }

            $household->memberRecords()->update(['is_head' => false]);
        }

        $member = HouseholdMember::create([
            'household_id' => $householdId,
            'resident_id'  => $residentId,
            'relationship' => HouseholdMember::valueFrom($relationship),
            'is_head'      => $isHead,
        ]);

        if ($by) {
            $this->activity->log(
                $by,
                'updated',
                'household',
                "Added resident #{$residentId} to household #{$householdId}",
                ['household_id' => $householdId, 'resident_id' => $residentId]
            );
        }

        return $member;
    }
}
