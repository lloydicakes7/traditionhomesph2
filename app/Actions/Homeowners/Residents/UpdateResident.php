<?php

namespace App\Actions\Homeowners\Residents;

use App\Models\Admin;
use App\Models\Resident;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class UpdateResident
{
    const PHOTO_DIRECTORY = 'photos/residents';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, array $data, $photo, bool $removePhoto, Admin $by): Resident
    {
        $resident = DB::transaction(function () use ($id, $data, $photo, $removePhoto) {
            $resident = Resident::findOrFail($id);
            $data = Resident::payloadFrom($data);

            if ($photo) {
                $data['profile_photo_path'] = StorageHelper::replacePhoto(
                    $photo,
                    $resident->profile_photo_path,
                    self::PHOTO_DIRECTORY
                );
            } elseif ($removePhoto) {
                StorageHelper::deletePhoto($resident->profile_photo_path);
                $data['profile_photo_path'] = null;
            }

            $resident->update($data);

            return $resident;
        });

        $this->activity->log(
            $by,
            'updated',
            'homeowner',
            "Updated resident {$resident->full_name}",
            ['resident_id' => $resident->id]
        );

        return $resident->fresh();
    }
}
