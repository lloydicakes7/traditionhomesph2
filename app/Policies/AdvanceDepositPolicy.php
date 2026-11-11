<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Credit held ahead of a bill — the Advance Deposits page.
 *
 * Taking a deposit and drawing it down are both edits of the credit, so they
 * read the module's permissions rather than inventing abilities nobody can be
 * granted separately.
 */
class AdvanceDepositPolicy
{
    const MODULE = 'Advance Deposits';

    /**
     * Determine whether the admin can browse the deposits page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one deposit.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can record money received ahead of a bill.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change a deposit.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can settle a bill from a unit's credit.
     */
    public function apply(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can return what is left of a deposit.
     */
    public function refund(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a deposit.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }
}
