<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Journal entries — the association's books.
 *
 * Every ability is a module permission, so the methods take the Admin alone and
 * the call sites name the class. `create` is the treasurer's own pen: writing
 * an entry by hand or posting a payment or expense is a different act from
 * reading the books, and it is the one that may be withheld from an officer who
 * should only be looking. `update`
 * changes an entry; `delete` removes a manual entry, and a module's posting is
 * still removed with its source.
 */
class JournalEntryPolicy
{
    const MODULE = 'Journal Entries';

    /**
     * Determine whether the admin can browse the journal.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can read one entry.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can write an entry by hand — an accrual, a
     * correction, an adjusting or closing entry.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change an entry.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a manual entry.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can send the journal out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }
}
