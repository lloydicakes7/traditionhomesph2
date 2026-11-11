<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CreateHousehold
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Household
    {
        if (! Household::propertyIsFree((int) $data['property_id'])) {
            throw new \RuntimeException('This property is already assigned to another household.');
        }

        if (HouseholdMember::residentIsPlaced((int) $data['head_resident_id'])) {
            throw new \RuntimeException('This resident already belongs to another household.');
        }

        $household = DB::transaction(function () use ($data) {
            $household = Household::create(Household::payloadFrom($data));

            $household->placeHead((int) $data['head_resident_id'], $data['relationship'] ?? null);

            return $household;
        });

        $this->activity->log(
            $by,
            'created',
            'household',
            "Created household #{$household->id}",
            ['household_id' => $household->id, 'resident_id' => $data['head_resident_id']]
        );

        return $household;
    }
}
