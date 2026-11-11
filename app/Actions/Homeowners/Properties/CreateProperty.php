<?php

namespace App\Actions\Homeowners\Properties;

use App\Models\Admin;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class CreateProperty
{
    const PHOTO_DIRECTORY = 'photos/properties';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $picture, Admin $by): Property
    {
        $data = Property::payloadFrom($data);

        if ($picture) {
            $data['picture'] = StorageHelper::storeFile($picture, self::PHOTO_DIRECTORY);
        }

        $property = Property::create($data);

        $this->activity->log(
            $by,
            'created',
            'property',
            "Created property {$property->block_lot_label}",
            ['property_id' => $property->id]
        );

        return $property;
    }
}
