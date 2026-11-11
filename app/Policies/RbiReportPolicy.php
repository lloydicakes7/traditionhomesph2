<?php

namespace App\Policies;

use App\Models\Admin;

class RbiReportPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('RBI Forms', 'View');
    }

    public function print(Admin $admin): bool
    {
        return $admin->hasPermission('RBI Forms', 'Print');
    }

    public function view(Admin $admin): bool
    {
        return $admin->hasPermission('RBI Reports', 'View');
    }

    public function export(Admin $admin): bool
    {
        return $admin->hasPermission('RBI Reports', 'Print');
    }
}
