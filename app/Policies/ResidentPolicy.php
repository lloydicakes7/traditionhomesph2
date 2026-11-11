<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Resident;
use App\Models\User;

/**
 * Residents (RBI profiles), on both sides of the system.
 *
 * The admin side answers "may this officer work with resident profiles?" and
 * checks the officer's permissions. The portal side (`*Own`) answers "is this
 * the resident this account was issued for?" — the whole identity of a portal
 * homeowner, and the only check the portal can make.
 */
class ResidentPolicy
{
    /**
     * Determine whether the admin can view any residents.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function viewAny(Admin $admin)
    {
        return $admin->hasPermission('Residents', 'View');
    }

    /**
     * Determine whether the admin can create residents.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function create(Admin $admin)
    {
        return $admin->hasPermission('Residents', 'Create');
    }

    /**
     * Determine whether the admin may open a resident's submitted valid ID.
     */
    public function viewValidId(Admin $admin, Resident $resident): bool
    {
        return $admin->hasPermission('Residents', 'View');
    }

    /**
     * Determine whether the admin can import residents data.
     */
    public function import(Admin $admin): bool
    {
        return $admin->hasPermission('Residents', 'Import');
    }

    /**
     * Determine whether the admin can export residents data.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission('Residents', 'Export');
    }

    /**
     * Determine whether the admin can update residents.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function update(Admin $admin)
    {
        return $admin->hasPermission('Residents', 'Edit');
    }

    /**
     * Determine whether the admin can delete residents.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function delete(Admin $admin)
    {
        return $admin->hasPermission('Residents', 'Delete');
    }

    /**
     * Determine whether the officer may print a resident's RBI Form B.
     */
    public function printRbi(Admin $admin): bool
    {
        return $admin->hasPermission('RBI Forms', 'Print');
    }

    // =====================================================================
    // Portal
    // =====================================================================

    /**
     * Determine whether the homeowner may read the profile: their own record.
     */
    public function viewOwn(User $user, Resident $resident): bool
    {
        return $user->resident == null || (int) $user->resident_id === (int) $resident->id;
    }

    /**
     * Determine whether the homeowner may print a resident's Form B.
     *
     * The head's own record and every member of their household qualify; no
     * other household's residents do, and an account with no household
     * qualifies for none.
     */
    public function printRbiOwn(User $user, Resident $resident): bool
    {
        if ($user->resident_id !== null && (int) $user->resident_id === (int) $resident->id) {
            return true;
        }

        $household = $user->household;

        if ($household === null) {
            return false;
        }

        return $household->memberRecords()
            ->where('resident_id', $resident->id)
            ->exists();
    }

    /**
     * Determine whether the homeowner may edit the profile: their own record.
     */
    public function updateOwn(User $user, Resident $resident): bool
    {
        return $user->resident == null || (int) $user->resident_id === (int) $resident->id;
    }
}
