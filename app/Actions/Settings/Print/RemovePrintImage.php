<?php

namespace App\Actions\Settings\Print;

use App\Actions\Settings\Print\Concerns\PrintSettingInput;
use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;

class RemovePrintImage
{
    use PrintSettingInput;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $side, Admin $by): void
    {
        $key = $this->imageKey($side);

        StorageHelper::deletePrivate(SettingsHelper::get($key, $this->group()));
        SettingsHelper::delete($key, $this->group());

        $this->activity->log($by, 'updated', 'Print Settings', 'Removed the ' . $side . ' print image');
    }
}
