<?php

namespace App\Actions\Settings\Authentication;

use App\Models\Admin;
use App\Queries\Settings\AuthenticationSettingsQuery;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;

class UpdateAuthenticationSettings
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): void
    {
        SettingsHelper::bulkSet([
            AuthenticationSettingsQuery::ALLOW_ADMIN_REGISTRATION    => ($data['allow_admin_registration'] ?? false) ? '1' : '0',
            AuthenticationSettingsQuery::ALLOW_RESIDENT_REGISTRATION => ($data['allow_resident_registration'] ?? false) ? '1' : '0',
            AuthenticationSettingsQuery::AUTO_ACCEPT_REGISTRATION    => ($data['auto_accept_registration'] ?? false) ? '1' : '0',
        ], AuthenticationSettingsQuery::GROUP);

        $this->activity->log($by, 'updated', 'settings', 'Updated the authentication settings');
    }
}
