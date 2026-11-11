<?php

namespace App\Queries\UserManagement\Roles;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Role::query()
            ->withCount('admins')
            ->orderBy('name')
            ->paginate($perPage);
    }
}
