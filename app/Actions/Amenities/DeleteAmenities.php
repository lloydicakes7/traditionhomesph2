<?php

namespace App\Actions\Amenities;

use App\Actions\Amenities\Concerns\AmenityPhoto;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteAmenities
{
    use AmenityPhoto;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $removable = Amenity::with('bookings')
            ->whereIn('id', $ids)
            ->get()
            ->filter(function (Amenity $amenity) {
                return $amenity->bookings->isEmpty();
            });

        if ($removable->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($removable) {
            $removable->each->delete();
        });

        foreach ($removable as $amenity) {
            $this->deletePhoto($amenity->image_path);

            $this->activity->log(
                $by,
                'deleted',
                'amenity',
                "Deleted amenity \"{$amenity->name}\"",
                ['amenity_id' => $amenity->id]
            );
        }

        return $removable->count();
    }
}
