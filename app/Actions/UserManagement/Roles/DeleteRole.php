<?php

namespace App\Actions\UserManagement\Roles;

use App\Models\Admin;
use App\Models\Role;
use App\Services\ActivityLogService;
use RuntimeException;

class DeleteRole
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Role $role, Admin $by): void
    {
        if ($blocker = $role->deletionBlocker()) {
            throw new RuntimeException($blocker);
        }

        $this->activity->log(
            $by,
            'deleted',
            'role',
            "Deleted role {$role->name}",
            ['role_id' => $role->id]
        );

        $role->delete();
    }
}
