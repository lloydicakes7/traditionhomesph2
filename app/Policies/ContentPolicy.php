<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Content;

class ContentPolicy
{
    const MODULE = 'Contents';

    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }
}
