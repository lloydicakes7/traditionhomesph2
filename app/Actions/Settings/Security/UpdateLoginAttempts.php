<?php

namespace App\Actions\Settings\Security;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;

class UpdateLoginAttempts
{
    const GROUP = 'security';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $maxAttempts, int $lockoutMinutes, Admin $by): void
    {
        SettingsHelper::bulkSet([
            'login_max_attempts'    => (string) $maxAttempts,
            'login_lockout_minutes' => (string) $lockoutMinutes,
        ], self::GROUP);

        $this->activity->log($by, 'updated', 'settings', 'Updated the login attempt settings');
    }
}
