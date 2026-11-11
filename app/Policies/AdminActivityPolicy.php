<?php

namespace App\Policies;

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;

class AdminActivityPolicy
{
    /**
     * Determine whether the admin can view any activities.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function viewAny(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }

    /**
     * Determine whether the admin can delete activities.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function delete(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }
}
