<?php

namespace App\Actions\UserManagement\Roles;

use App\Actions\UserManagement\Roles\Concerns\RolePermissions;
use App\Models\Admin;
use App\Models\Role;
use App\Services\ActivityLogService;

class UpdateRole
{
    use RolePermissions;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Role $role, array $data, array $permissionKeys, Admin $by): Role
    {
        if ($role->hasFullAccess()) {
            $role->update(['description' => $data['description'] ?? $role->description]);
        } else {
            $role->update(array_merge($data, ['permissions' => array_values($permissionKeys)]));
        }

        $this->activity->log(
            $by,
            'updated',
            'role',
            "Updated role {$role->name}",
            ['role_id' => $role->id]
        );

        return $role;
    }
}
