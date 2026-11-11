<?php

namespace App\Actions\Amenities;

use App\Actions\Amenities\Concerns\AmenityPayload;
use App\Actions\Amenities\Concerns\AmenityPhoto;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateAmenity
{
    use AmenityPayload;
    use AmenityPhoto;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(
        Amenity $amenity,
        array $data,
        array $week,
        $photo,
        bool $removeImage,
        Admin $by
    ): Amenity {
        $payload = $this->payload($data);

        if ($photo) {
            $payload['image_path'] = $this->replacePhoto($photo, $amenity->image_path);
        } elseif ($removeImage) {
            $this->deletePhoto($amenity->image_path);
            $payload['image_path'] = null;
        }

        DB::transaction(function () use ($amenity, $payload, $week) {
            $amenity->update($payload);

            $this->saveWeek($amenity, $week);
        });

        $this->activity->log(
            $by,
            'updated',
            'amenity',
            "Updated amenity \"{$amenity->name}\"",
            ['amenity_id' => $amenity->id]
        );

        return $amenity;
    }
}
