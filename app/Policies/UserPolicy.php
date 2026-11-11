<?php

namespace App\Policies;

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the admin can view any homeowners.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'View');
    }

    /**
     * Determine whether the admin can create homeowners.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Create');
    }

    /**
     * Determine whether the admin can import homeowners.
     */
    public function import(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Import');
    }

    /**
     * Determine whether the admin can export homeowners data.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Export');
    }

    /**
     * Determine whether the admin can update homeowners.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Edit');
    }

    /**
     * Determine whether the admin can delete homeowners.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Delete');
    }

    /**
     * Determine whether the admin can enable or disable a homeowner's account.
     *
     * Disabling is a change to the account's own record, so it rides the same
     * Edit permission as every other account edit; the action reads the account
     * under the Homeowners module, never a role of its own.
     */
    public function toggleActive(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Edit');
    }

    /**
     * Determine whether the admin may issue a new set-password link.
     *
     * The officer never sees or chooses the password: this ability only allows
     * the account's own password to be invalidated and a link to be mailed.
     */
    public function resetPassword(Admin $admin): bool
    {
        return $admin->hasPermission('Homeowners', 'Edit');
    }

    // =====================================================================
    // Client
    // =====================================================================

    /**
     * Determine whether the homeowner may read the account: their own.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $account
     * @return bool
     */
    public function viewOwn(User $user, User $account): bool
    {
        return (int) $user->id === (int) $account->id;
    }

    /**
     * Determine whether the homeowner may edit the account: their own, and only
     * the contact details and password the portal offers.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $account
     * @return bool
     */
    public function updateOwn(User $user, User $account): bool
    {
        return (int) $user->id === (int) $account->id;
    }
}
