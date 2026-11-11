<?php

namespace App\Actions\Settings\Security;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;

class UpdatePasswordPolicy
{
    const GROUP = 'security';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): void
    {
        SettingsHelper::bulkSet([
            'password_min_length'     => (string) $data['password_min_length'],
            'password_require_upper'  => $data['password_require_upper'] ? '1' : '0',
            'password_require_lower'  => $data['password_require_lower'] ? '1' : '0',
            'password_require_number' => $data['password_require_number'] ? '1' : '0',
            'password_require_symbol' => $data['password_require_symbol'] ? '1' : '0',
            'password_expiry_days'    => (string) $data['password_expiry_days'],
        ], self::GROUP);

        $this->activity->log($by, 'updated', 'settings', 'Updated the password policy');
    }
}
