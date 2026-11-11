<?php

namespace App\Actions\Settings\General;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;

class RemoveHoaLogo
{
    const GROUP = 'hoa_info';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(?string $currentLogoPath, Admin $by): void
    {
        if ($currentLogoPath) {
            StorageHelper::deleteFile($currentLogoPath);
            SettingsHelper::set('logo_path', '', self::GROUP);
        }

        $this->activity->log($by, 'updated', 'settings', 'Removed HOA logo');
    }
}
