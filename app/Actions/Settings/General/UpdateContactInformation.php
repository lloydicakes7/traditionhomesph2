<?php

namespace App\Actions\Settings\General;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;

class UpdateContactInformation
{
    const GROUP = 'hoa_info';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): void
    {
        SettingsHelper::bulkSet(array_map(function ($value) {
            return $value ?? '';
        }, $data), self::GROUP);

        $this->activity->log($by, 'updated', 'settings', 'Updated contact information');
    }
}
