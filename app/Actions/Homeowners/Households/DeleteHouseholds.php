<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Resident;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteHouseholds
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle($ids, Admin $by): void
    {
        foreach ((array) $ids as $id) {
            $this->deleteOne((int) $id, $by);
        }
    }

    // ===== Helpers =====

    private function deleteOne(int $id, Admin $by): void
    {
        $household = Household::with('memberRecords')->findOrFail($id);
        $headResidentId = (int) $household->head_resident?->id;

        $orphans = [];

        foreach ($household->memberRecords as $record) {
            $residentId = (int) $record->resident_id;

            if (! $residentId || $residentId === $headResidentId) {
                continue;
            }

            $elsewhere = HouseholdMember::where('resident_id', $residentId)
                ->where('household_id', '!=', $household->id)
                ->exists();

            if (! $elsewhere) {
                $orphans[] = $residentId;
            }
        }

        DB::transaction(function () use ($household, $orphans) {
            HouseholdMember::where('household_id', $household->id)->delete();
            $household->delete();

            if ($orphans !== []) {
                Resident::whereIn('id', $orphans)->delete();
            }
        });

        $this->activity->log(
            $by,
            'deleted',
            'household',
            "Deleted household #{$id}",
            ['household_id' => $id]
        );
    }
}
