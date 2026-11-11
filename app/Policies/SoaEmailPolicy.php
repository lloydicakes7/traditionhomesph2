<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Statements of account — the Email SOA page.
 *
 * Preparing a batch and mailing it are both creations of statements, so they
 * read the module's Create permission rather than inventing abilities nobody
 * can be granted separately.
 */
class SoaEmailPolicy
{
    const MODULE = 'Email SOA';

    /**
     * Determine whether the admin can browse the statements page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one statement.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can prepare a period's statements.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can mail statements.
     */
    public function send(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can remove statements from the list.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can send statements out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
