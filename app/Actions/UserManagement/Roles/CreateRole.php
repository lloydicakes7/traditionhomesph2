<?php

namespace App\Actions\UserManagement\Roles;

use App\Actions\UserManagement\Roles\Concerns\RolePermissions;
use App\Models\Admin;
use App\Models\Role;
use App\Services\ActivityLogService;

class CreateRole
{
    use RolePermissions;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, array $permissionKeys, Admin $by): Role
    {
        $role = Role::create(array_merge($data, [
            'permissions' => $this->storedPermissions($data['name'] ?? null, $permissionKeys),
        ]));

        $this->activity->log(
            $by,
            'created',
            'role',
            "Created role {$role->name}",
            ['role_id' => $role->id]
        );

        return $role;
    }
}
