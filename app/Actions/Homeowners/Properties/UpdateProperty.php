<?php

namespace App\Actions\Homeowners\Properties;

use App\Models\Admin;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class UpdateProperty
{
    const PHOTO_DIRECTORY = 'photos/properties';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, array $data, $picture, bool $removePicture, Admin $by): Property
    {
        $property = Property::findOrFail($id);

        $data = Property::payloadFrom($data);

        if ($picture) {
            StorageHelper::deleteFile($property->picture);
            $data['picture'] = StorageHelper::storeFile($picture, self::PHOTO_DIRECTORY);
        } elseif ($removePicture) {
            StorageHelper::deleteFile($property->picture);
            $data['picture'] = null;
        }

        $property->update($data);

        $this->activity->log(
            $by,
            'updated',
            'property',
            "Updated property {$property->block_lot_label}",
            ['property_id' => $property->id]
        );

        return $property->fresh();
    }
}
