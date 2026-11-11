<?php

namespace App\Actions\Amenities\Concerns;

use App\Models\Amenity\Amenity;
use App\Utility\StorageHelper;

trait AmenityPhoto
{
    // ===== Photo =====

    private function storePhoto($photo): string
    {
        return StorageHelper::storePhoto($photo, Amenity::IMAGE_DIRECTORY);
    }

    private function replacePhoto($photo, ?string $current): string
    {
        return StorageHelper::replacePhoto($photo, $current, Amenity::IMAGE_DIRECTORY);
    }

    private function deletePhoto(?string $path): void
    {
        StorageHelper::deletePhoto($path);
    }
}
