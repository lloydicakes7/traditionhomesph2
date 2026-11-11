<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Property;
use App\Models\User;

/**
 * Properties, on both sides of the system.
 *
 * The admin side answers "may this officer work with properties?" and checks the
 * officer's permissions. The portal side (`*Own`) answers "is this the unit the
 * homeowner's household occupies?" — ownership here is the household link, not
 * the deed, because that is what the system records.
 */
class PropertyPolicy
{
    /**
     * Determine whether the admin can view any properties.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('Properties', 'View');
    }

    /**
     * Determine whether the admin can create properties.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('Properties', 'Create');
    }

    /**
     * Determine whether the admin can import properties.
     */
    public function import(Admin $admin): bool
    {
        return $admin->hasPermission('Properties', 'Import');
    }

    /**
     * Determine whether the admin can export properties data.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission('Properties', 'Export');
    }

    /**
     * Determine whether the admin can update properties.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission('Properties', 'Edit');
    }

    /**
     * Determine whether the admin can delete properties.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission('Properties', 'Delete');
    }

    // =====================================================================
    // Portal side — the unit the homeowner's household occupies
    // =====================================================================

    /**
     * Determine whether the homeowner may read the property: the one their
     */
    public function viewOwn(User $user, Property $property): bool
    {
        return $user->household ? (int) $user->household->property_id === (int) $property->id : false;
    }
}
