<?php

namespace App\Actions\Settings\Print;

use App\Actions\Settings\Print\Concerns\PrintSettingInput;
use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;

class UpdatePrintSettings
{
    use PrintSettingInput;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): void
    {
        $stored = $this->storePayload($data);

        if ($stored !== []) {
            SettingsHelper::bulkSet($stored, $this->group());
        }

        $this->activity->log($by, 'updated', 'Print Settings', 'Updated the shared print letterhead and footer');
    }
}
