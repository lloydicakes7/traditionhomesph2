<?php

namespace App\Queries\UserManagement\Roles;

use App\Models\Role;

class RoleFormQuery
{
    // ===== Queries =====

    public function find(int $id): Role
    {
        return Role::findOrFail($id);
    }
}
