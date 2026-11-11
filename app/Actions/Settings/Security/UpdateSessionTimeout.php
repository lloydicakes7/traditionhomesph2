<?php

namespace App\Actions\Settings\Security;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;

class UpdateSessionTimeout
{
    const GROUP = 'security';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $timeout, bool $expireOnClose, Admin $by): void
    {
        SettingsHelper::bulkSet([
            'session_timeout'         => (string) $timeout,
            'session_expire_on_close' => $expireOnClose ? '1' : '0',
        ], self::GROUP);

        $this->activity->log($by, 'updated', 'settings', 'Updated the session timeout settings');
    }
}
