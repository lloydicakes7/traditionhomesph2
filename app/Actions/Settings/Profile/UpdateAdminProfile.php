<?php

namespace App\Actions\Settings\Profile;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class UpdateAdminProfile
{
    const PHOTO_DIRECTORY = 'profile-photos';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Admin $admin, array $data, $photo): Admin
    {
        $admin->name = $data['name'];
        $admin->email = $data['email'];

        if ($photo) {
            $admin->profile_photo_path = StorageHelper::storeFile($photo, self::PHOTO_DIRECTORY);
        }

        $admin->save();

        $this->activity->log(
            $admin,
            'updated',
            'profile',
            'Updated personal information' . ($photo ? ' and profile photo' : ''),
            ['user_id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email]
        );

        return $admin;
    }
}
