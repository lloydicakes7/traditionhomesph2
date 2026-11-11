<?php

namespace App\Actions\UserManagement\Users;

use App\Models\Admin;
use App\Services\ActivityLogService;

class DeleteAdmin
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Admin $admin, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'user',
            "Deleted admin {$admin->name} ({$admin->email})",
            ['admin_id' => $admin->id]
        );

        $admin->delete();
    }
}
