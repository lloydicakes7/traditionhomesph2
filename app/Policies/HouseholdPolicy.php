<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\User;

/**
 * Households, on both sides of the system.
 *
 * The admin side answers "may this officer work with households?" and checks the
 * officer's permissions. The portal side (`*Own`) answers "is this the
 * homeowner's own household?" and compares ownership — a homeowner has no role
 * to check against.
 */
class HouseholdPolicy
{
    /**
     * Determine whether the admin can view any households.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'View');
    }

    /**
     * Determine whether the admin can view a household.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'View');
    }

    /**
     * Determine whether the admin can create households.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'Create');
    }

    /**
     * Determine whether the admin can import households.
     */
    public function import(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'Import');
    }

    /**
     * Determine whether the admin can export households.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'Export');
    }

    /**
     * Determine whether the admin can update a household.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'Edit');
    }

    /**
     * Determine whether the admin can delete a household.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission('Households', 'Delete');
    }

    /**
     * Determine whether the officer may print a household's RBI Form A.
     */
    public function printRbi(Admin $admin): bool
    {
        return $admin->hasPermission('RBI Forms', 'Print');
    }

    // =====================================================================
    // Client
    // =====================================================================

    /**
     * Determine whether the homeowner may read the household.
     */
    public function viewOwn(User $user, Household $household): bool
    {
        return (int) $user->household?->id === (int) $household->id;
    }

    /**
     * Determine whether the homeowner may print their own household's Form A.
     *
     * A head with no household on file owns nothing, so nothing is printable.
     */
    public function printRbiOwn(User $user, Household $household): bool
    {
        $own = $user->household;

        return $own !== null && (int) $own->id === (int) $household->id;
    }

    /**
     * Determine whether the homeowner may change the household or who lives in
     * it: only the household head, who owns the record for the household.
     */
    public function updateOwn(User $user, Household $household): bool
    {
        $head = $household->head_resident;

        return $head !== null && (int) $user->resident_id === (int) $head->id;
    }
}
