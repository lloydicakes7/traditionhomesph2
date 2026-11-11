<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\User;

/**
 * Amenities, on both sides of the system.
 *
 * The admin side answers "may this officer manage the association's bookable
 * facilities?" and checks the officer's permissions. No admin-side ability
 * depends on which facility is in hand, so those methods take the Admin alone
 * and every call site names the class (`@can('update', Amenity::class)`, the
 * form the rest of the panel uses). The portal side answers "may this homeowner
 * request this facility?" and does need the record: a facility the office has
 * not put on the portal cannot be requested, whatever the account.
 */
class AmenityPolicy
{
    const MODULE = 'Amenities';

    /**
     * Determine whether the admin can browse the amenities page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one amenity.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can add an amenity.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change an amenity — its rate, its
     * schedule, its deposit rule or its portal visibility.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove an amenity.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    // =====================================================================
    // Portal side — the homeowner requesting a facility
    // =====================================================================

    /**
     * Determine whether this homeowner may request this amenity: only the
     * facilities the office has opened to the portal.
     */
    public function book(User $user, Amenity $amenity): bool
    {
        return (bool) $amenity->is_bookable;
    }
}
