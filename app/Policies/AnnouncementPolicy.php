<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Announcement;

/**
 * Admin-side policy for announcements.
 *
 * Announcements are officer-managed data gated on the "Announcements" module
 * permissions — no ownership rule applies here (that only exists portal-side).
 */
class AnnouncementPolicy
{
    const MODULE = 'Announcements';

    /**
     * Determine whether the admin can browse the announcements page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one announcement.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can publish announcements.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can edit announcements.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove announcements.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }
}
