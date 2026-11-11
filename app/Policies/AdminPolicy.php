<?php

namespace App\Policies;

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;

class AdminPolicy
{
    /**
     * Determine whether the admin can view any admins (list users).
     *
     * @param  \App\Models\Admin  $current
     * @return bool
     */
    public function viewAny(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }

    /**
     * Determine whether the admin can view another admin.
     *
     * @param  \App\Models\Admin  $current
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function view(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR()) || $admin->id === auth()->id();
    }

    /**
     * Determine whether the admin can edit their own profile.
     *
     * @param  \App\Models\Admin  $current
     * @return bool
     */
    public function edit(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR()) || $admin->id === auth()->id();
    }

    /**
     * Determine whether the admin can create other admins.
     *
     * @param  \App\Models\Admin  $current
     * @return bool
     */
    public function create(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }

    /**
     * Determine whether the admin can update another admin.
     *
     * @param  \App\Models\Admin  $current
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function update(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR()) || $admin->id === auth()->id();
    }

    /**
     * Determine whether the admin can change another admin's role.
     *
     * @param  \App\Models\Admin  $current
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function updateRole(Admin $current, Admin $admin)
    {
        return $current->hasRole(DefaultRoleSlug::ADMINISTRATOR()) && $current->id !== $admin->id;
    }

    /**
     * Determine whether the admin can toggle the active status.
     *
     * @param  \App\Models\Admin  $current
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function toggleActive(Admin $current, Admin $admin)
    {
        return $current->hasRole(DefaultRoleSlug::ADMINISTRATOR()) && $current->id !== $admin->id;
    }

    /**
     * Determine whether the admin can issue a new password link to another
     * officer.
     *
     * The office never sees or chooses a password: the ability only allows the
     * account's own password to be invalidated and the link to be mailed. An
     * officer changes their own password from their profile.
     *
     * @param  \App\Models\Admin  $current
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function resetPassword(Admin $current, Admin $admin)
    {
        return $current->hasRole(DefaultRoleSlug::ADMINISTRATOR()) && $current->id !== $admin->id;
    }

    /**
     * Determine whether the admin can delete another admin.
     *
     * @param  \App\Models\Admin  $current
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function delete(Admin $current, Admin $admin)
    {
        return $current->hasRole(DefaultRoleSlug::ADMINISTRATOR())
            && $current->id !== $admin->id
            && ! $admin->isLastAdministrator();
    }

    /**
     * Determine whether the admin can manage roles.
     *
     * @param  \App\Models\Admin  $current
     * @return bool
     */
    public function manageRoles(Admin $current)
    {
        return $current->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }
}
