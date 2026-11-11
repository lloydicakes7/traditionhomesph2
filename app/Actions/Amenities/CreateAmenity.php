<?php

namespace App\Actions\Amenities;

use App\Actions\Amenities\Concerns\AmenityPayload;
use App\Actions\Amenities\Concerns\AmenityPhoto;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CreateAmenity
{
    use AmenityPayload;
    use AmenityPhoto;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, array $week, $photo, Admin $by): Amenity
    {
        $payload = $this->payload($data);

        if ($photo) {
            $payload['image_path'] = $this->storePhoto($photo);
        }

        $amenity = DB::transaction(function () use ($payload, $week) {
            $amenity = Amenity::create($payload);

            $this->saveWeek($amenity, $week);

            return $amenity;
        });

        $this->activity->log(
            $by,
            'created',
            'amenity',
            "Added amenity \"{$amenity->name}\"",
            ['amenity_id' => $amenity->id]
        );

        return $amenity;
    }
}
