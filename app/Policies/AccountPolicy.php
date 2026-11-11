<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * The chart of accounts — the structure the association keeps its books in.
 *
 * Every ability here is a module permission rather than a rule about which
 * account is in hand, so the methods take the Admin alone and every call site
 * names the class (`@can('create', Account::class)`), the form the rest of the
 * panel uses. An account in use is retired by editing it, never deleted; only
 * an account already retired may be removed, and `delete` is that last step —
 * the service refuses it until the account is switched off.
 */
class AccountPolicy
{
    const MODULE = 'Chart of Accounts';

    /**
     * Determine whether the admin can browse the chart of accounts.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one account.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can add an account to the chart.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change or retire an account.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a retired account from the chart.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can send the chart out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
