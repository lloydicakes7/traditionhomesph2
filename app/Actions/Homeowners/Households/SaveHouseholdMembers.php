<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class SaveHouseholdMembers
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $householdId, array $members, Admin $by): void
    {
        $household = Household::findOrFail($householdId);

        $clean = $this->clean($members);

        DB::transaction(function () use ($household, $clean) {
            $household->memberRecords()->delete();

            $headPlaced = false;

            foreach ($clean as $member) {
                $isHead = ! empty($member['is_head']) && ! $headPlaced;
                $headPlaced = $headPlaced || $isHead;

                HouseholdMember::create([
                    'household_id' => $household->id,
                    'resident_id'  => $member['resident_id'],
                    'relationship' => HouseholdMember::valueFrom($member['relationship'] ?? null),
                    'is_head'      => $isHead,
                ]);
            }
        });

        $this->activity->log(
            $by,
            'updated',
            'household',
            "Updated members for household #{$householdId}",
            ['household_id' => $householdId, 'member_count' => count($clean)]
        );
    }

    // ===== Helpers =====

    /**
     * One row per resident, in the order the form listed them.
     */
    private function clean(array $members): array
    {
        $seen = [];
        $clean = [];

        foreach ($members as $member) {
            $residentId = (string) ($member['resident_id'] ?? '');

            if ($residentId === '' || isset($seen[$residentId])) {
                continue;
            }

            $seen[$residentId] = true;
            $clean[] = $member;
        }

        return $clean;
    }
}
