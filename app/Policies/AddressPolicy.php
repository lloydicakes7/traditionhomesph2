<?php

namespace App\Policies;

use App\Models\Admin;

class AddressPolicy
{
    /**
     * Determine whether the admin can view any addresses.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('Addresses', 'View');
    }

    /**
     * Determine whether the admin can create addresses.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('Addresses', 'Create');
    }

    /**
     * Determine whether the admin can update addresses.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission('Addresses', 'Edit');
    }

    /**
     * Determine whether the admin can delete addresses.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission('Addresses', 'Delete');
    }
}
