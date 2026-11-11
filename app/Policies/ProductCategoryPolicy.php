<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * The groups products and services are filed under — the Product Categories
 * page, and the category select on the item form.
 */
class ProductCategoryPolicy
{
    const MODULE = 'Product Categories';

    /**
     * Determine whether the admin can browse the categories page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one category.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can add a category.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change a category.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a category.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }
}
