<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\HoaMember;

/**
 * Admin-side policy for HOA members.
 *
 * Members are officer-managed data gated on the "Members" module permissions —
 * no ownership rule applies here (that only exists portal-side).
 */
class HoaMemberPolicy
{
    const MODULE = 'Members';

    /**
     * Determine whether the admin can browse the members page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one member.
     */
    public function view(Admin $admin, HoaMember $member): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can add members.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can edit members.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove members.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }
}
