<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Services\ActivityLogService;

class RemoveHouseholdMember
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $householdId, int $residentId, ?Admin $by = null): void
    {
        $household = Household::findOrFail($householdId);

        $household->memberRecords()
            ->where('resident_id', $residentId)
            ->delete();

        if ($by) {
            $this->activity->log(
                $by,
                'updated',
                'household',
                "Removed resident #{$residentId} from household #{$householdId}",
                ['household_id' => $householdId, 'resident_id' => $residentId]
            );
        }
    }
}
