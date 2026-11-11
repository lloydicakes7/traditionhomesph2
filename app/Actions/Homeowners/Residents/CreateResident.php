<?php

namespace App\Actions\Homeowners\Residents;

use App\Models\Admin;
use App\Models\Resident;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class CreateResident
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $photo, Admin $by): Resident
    {
        $resident = DB::transaction(function () use ($data, $photo) {
            $data = Resident::payloadFrom($data);

            if ($photo) {
                $data['profile_photo_path'] = StorageHelper::storeResidentPhoto($photo);
            }

            return Resident::create($data);
        });

        $this->activity->log(
            $by,
            'created',
            'homeowner',
            "Created resident {$resident->full_name}",
            ['resident_id' => $resident->id]
        );

        return $resident;
    }
}
