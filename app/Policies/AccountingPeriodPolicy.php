<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Accounting periods — the month-end and year-end lock.
 *
 * Every ability is a module permission, so the methods take the Admin alone.
 * `close` and `reopen` are deliberately their own abilities: closing a month
 * stops every module from posting into it and is what makes a set of financial
 * statements mean something, and reopening one puts a locked month back in
 * play — neither is the same authority as reading the register. A period row
 * is never deleted, so there is no delete ability here.
 */
class AccountingPeriodPolicy
{
    const MODULE = 'Period Close';

    /**
     * Determine whether the admin can read the period register.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can read one period.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can close a month.
     */
    public function close(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Close');
    }

    /**
     * Determine whether the admin can reopen a closed month.
     */
    public function reopen(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Reopen');
    }
}
