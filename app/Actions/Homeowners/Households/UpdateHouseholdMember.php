<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Resident;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class UpdateHouseholdMember
{
    const PHOTO_DIRECTORY = 'photos/residents';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $residentId, int $householdId, array $data, $photo, ?Admin $by = null): void
    {
        DB::transaction(function () use ($residentId, $householdId, $data, $photo) {
            $resident = Resident::findOrFail($residentId);
            $payload = Resident::payloadFrom($data);

            if ($photo) {
                $payload['profile_photo_path'] = StorageHelper::replacePhoto(
                    $photo,
                    $resident->profile_photo_path,
                    self::PHOTO_DIRECTORY
                );
            }

            $resident->update($payload);

            $household = Household::findOrFail($householdId);
            $member = $household->memberRecords()
                ->where('resident_id', $residentId)
                ->first();

            if ($member === null) {
                return;
            }

            $this->updateMemberRecord($household, $member, $data);
        });

        if ($by) {
            $this->activity->log(
                $by,
                'updated',
                'household',
                "Updated resident #{$residentId} in household #{$householdId}",
                ['resident_id' => $residentId, 'household_id' => $householdId]
            );
        }
    }

    // ===== Helpers =====

    private function updateMemberRecord(Household $household, HouseholdMember $member, array $data): void
    {
        if (array_key_exists('relationship', $data)) {
            $member->relationship = HouseholdMember::valueFrom($data['relationship']);
        }

        $member->save();

        if (! empty($data['is_head'])) {
            $household->designateHead($member->resident_id);

            return;
        }

        if (array_key_exists('is_head', $data)) {
            $member->update(['is_head' => false]);
        }
    }
}
