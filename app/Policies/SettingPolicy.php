<?php

namespace App\Policies;

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;

/**
 * Admin-side policy for the association's settings.
 *
 * Settings are managed from the admin panel, so this policy receives the
 * authenticated `Admin` (guard `admin`) — the portal `User` never touches
 * settings, and the two account types are never merged (see AGENTS.md).
 */
class SettingPolicy
{
    /**
     * Determine whether the admin can view the settings pages.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function viewAny(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }

    /**
     * Determine whether the admin can update settings.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function update(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }

    /**
     * Determine whether the admin can download backups.
     *
     * @param  \App\Models\Admin  $admin
     * @return bool
     */
    public function download(Admin $admin)
    {
        return $admin->hasRole(DefaultRoleSlug::ADMINISTRATOR());
    }
}
