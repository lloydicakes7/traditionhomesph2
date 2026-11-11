<?php

namespace App\Queries\UserManagement\Users;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class UserFormQuery
{
    // ===== Options =====

    public function roles(): Collection
    {
        return Role::orderBy('name')->get(['id', 'name']);
    }
}
