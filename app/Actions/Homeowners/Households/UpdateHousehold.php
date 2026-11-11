<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateHousehold
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, array $data, Admin $by): Household
    {
        $household = Household::findOrFail($id);

        if (! Household::propertyIsFree((int) $data['property_id'], $household->id)) {
            throw new \RuntimeException('This property is already assigned to another household.');
        }

        DB::transaction(function () use ($household, $data) {
            $household->update(Household::payloadFrom($data));

            if (! empty($data['head_resident_id'])) {
                $this->placeHead($household, $data);
            }
        });

        $this->activity->log(
            $by,
            'updated',
            'household',
            "Updated household #{$household->id}",
            ['household_id' => $household->id]
        );

        return $household;
    }

    // ===== Helpers =====

    private function placeHead(Household $household, array $data): void
    {
        $residentId = (int) $data['head_resident_id'];

        if (HouseholdMember::residentIsPlaced($residentId, $household->id)) {
            throw new \RuntimeException('This resident already belongs to another household.');
        }

        $household->placeHead($residentId, $data['relationship'] ?? null);
    }
}
