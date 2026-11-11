<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Violation;

/**
 * Violations, on both sides of the system.
 *
 * The admin side answers "may this officer work with violations?" — the module
 * permission gates the page, and issuing, editing, resolving and withdrawing a
 * violation are all changes to the same record, so they read the same
 * permission rather than inventing abilities nobody can be granted separately.
 *
 * The portal side answers the two questions a homeowner can ask: "is this
 * violation against my household?" and "may I acknowledge it?". A homeowner
 * never sees another household's violations, never resolves one and never
 * issues one — but a notice addressed to them is theirs to confirm receiving.
 */
class ViolationPolicy
{
    const MODULE = 'Violations';

    // =====================================================================
    // Admin side — the office's own record
    // =====================================================================

    /**
     * Determine whether the admin can browse the violations page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one violation.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can issue a violation and file the
     * optional office report with it.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change a violation after it was issued.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can resolve a violation — close it as
     * addressed and move it into the member's history.
     */
    public function resolve(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a violation from the record.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    // =====================================================================
    // Portal side — the homeowner's own notices
    // =====================================================================

    /**
     * Determine whether this homeowner may read the violation: their own
     * household's.
     */
    public function viewOwn(User $user, Violation $violation): bool
    {
        return $this->owns($user, $violation);
    }

    /**
     * Determine whether this homeowner may acknowledge the notice: it is theirs,
     */
    public function acknowledgeOwn(User $user, Violation $violation): bool
    {
        return $this->owns($user, $violation) && ! $violation->is_acknowledged;
    }

    /**
     * Whether the violation belongs to the signed-in homeowner's household.
     */
    private function owns(User $user, Violation $violation): bool
    {
        return $user->resident_id !== null && $violation->resident_id === $user->resident_id;
    }
}
