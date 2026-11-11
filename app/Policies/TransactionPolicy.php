<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Transactions — the books read a line at a time, for auditing and searching.
 *
 * The register is a *view* of other records rather than a record of its own, so
 * there is no model to hang a policy on: the page authorizes against this class
 * through the `transaction.*` gates registered in
 * \\App\\Providers\\AuthServiceProvider, the same shape the Reports suite uses.
 * It reads and exports and nothing else — the movements are written where they
 * are raised.
 */
class TransactionPolicy
{
    const MODULE = 'Transactions';

    /**
     * Determine whether the admin can read the transactions register.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can send the register out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
