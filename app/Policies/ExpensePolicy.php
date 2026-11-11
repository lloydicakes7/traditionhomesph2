<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Expense\Expense;

class ExpensePolicy
{
    const MODULE = 'Expenses';

    /**
     * Determine whether the admin can browse the expenses page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one expense.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can log an expense.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change an expense.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can move an expense through its approval
     * gate (submit, sign off, return).
     */
    public function approve(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Approve');
    }

    /**
     * Determine whether the admin can remove an expense.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can send expenses out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
