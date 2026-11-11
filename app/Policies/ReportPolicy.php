<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * The Reports suite — everything the books and the community data roll up into.
 *
 * A report is not a record, so there is no model for a policy to hang off:
 * this one is registered as the two gates `report.viewAny` and `report.export`
 * (see \App\Providers\AuthServiceProvider), which is what the Reports pages
 * authorize against and what the sidebar's menu entries are filtered by. The
 * shape is the same as every other policy in the app — a module permission,
 * read through `Admin::hasPermission()` — so nothing about a report's
 * authorization is special except that it names no class.
 */
class ReportPolicy
{
    const MODULE = 'Reports';

    /**
     * Determine whether the admin can read the report pages.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can download and print a report.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
