<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * What the association sells and performs — the Products and Services page and
 * the quick-add form on the Add Invoice modal.
 *
 * Adding an item from inside an invoice needs the same permission as adding it
 * from its own page, so both read the module's Create ability rather than the
 * invoice form inventing an ability of its own.
 */
class ProductPolicy
{
    const MODULE = 'Products and Services';

    /**
     * Determine whether the admin can browse the products page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one item.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can add an item.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change an item.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove an item.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }
}
