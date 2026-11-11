<?php

namespace App\Actions\Settings\Profile;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class RemoveAdminProfilePhoto
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Admin $admin): void
    {
        if ($admin->profile_photo_path) {
            StorageHelper::deleteFile($admin->profile_photo_path);
            $admin->profile_photo_path = null;
            $admin->save();
        }

        $this->activity->log($admin, 'updated', 'profile', 'Removed profile photo', ['user_id' => $admin->id]);
    }
}
