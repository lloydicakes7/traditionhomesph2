<?php

namespace App\Actions\UserManagement\Users;

use App\Models\Admin;
use App\Services\ActivityLogService;

class ToggleAdminActive
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Admin $admin, Admin $by): bool
    {
        $admin->update(['is_active' => ! $admin->is_active]);

        $this->activity->log(
            $by,
            'toggled',
            'user',
            ($admin->is_active ? 'Enabled' : 'Disabled') . " admin {$admin->name} ({$admin->email})",
            ['admin_id' => $admin->id, 'is_active' => $admin->is_active]
        );

        return $admin->is_active;
    }
}
