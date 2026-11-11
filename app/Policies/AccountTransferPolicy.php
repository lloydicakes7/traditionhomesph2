<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Transfers — money moved between the association's own accounts.
 *
 * Every ability is a module permission, so the methods take the Admin alone and
 * the call sites name the class. Moving the association's money from one of its
 * accounts to another is its own act — it posts to the books, like recording a
 * receipt does — which is why `create` is a permission the office can grant or
 * withhold on its own.
 */
class AccountTransferPolicy
{
    const MODULE = 'Transfers';

    /**
     * Determine whether the admin can browse the transfers register.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can read one transfer.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can record a transfer.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can remove a transfer.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can send the register out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
