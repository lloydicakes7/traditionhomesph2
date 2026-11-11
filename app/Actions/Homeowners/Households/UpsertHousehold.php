<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpsertHousehold
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $propertyId, array $data, Admin $by): ?Household
    {
        $household = Household::where('property_id', $propertyId)->first();

        if (! Household::propertyIsFree($propertyId, $household?->id)) {
            return null;
        }

        $household = DB::transaction(function () use ($propertyId, $data, $household) {
            $household = Household::updateOrCreate(
                ['property_id' => $propertyId],
                Household::payloadFrom([
                    'property_id'  => $propertyId,
                    'type'         => $data['type'] ?? 'owner',
                    'prev_address' => $data['prev_address'] ?? null,
                ])
            );

            if (! empty($data['head_resident_id'])) {
                $household->placeHead((int) $data['head_resident_id'], $data['relationship'] ?? null);
            }

            return $household;
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
}
