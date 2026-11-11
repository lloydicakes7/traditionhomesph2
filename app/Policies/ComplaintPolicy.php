<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Complaint;
use App\Models\User;

/**
 * Complaints, on both sides of the system.
 *
 * The admin side answers "may this officer work with complaints?" — the module
 * permission gates the page, and responding, changing a status and closing a
 * complaint are all changes to the same record, so they read the same
 * permission rather than inventing abilities nobody can be granted separately.
 *
 * The portal side answers the two questions a homeowner can ask: "is this
 * complaint mine?" and "may I file one?". A homeowner reads their own
 * submissions and their updates, files new ones, and reads nothing else — the
 * office's queue belongs to the office.
 */
class ComplaintPolicy
{
    const MODULE = 'Complaints';

    // =====================================================================
    // Admin side — the office's queue
    // =====================================================================

    /**
     * Determine whether the admin can browse the complaints page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one complaint.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can file a complaint for a report made to
     * the office.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change a complaint after it was filed.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can answer a complaint and move its status.
     * Closing is part of that move — a response may carry the Closed status —
     * so closing has no separate ability nobody can be granted apart from it.
     */
    public function respond(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a complaint from the queue.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    // =====================================================================
    // Portal side — the homeowner's own submissions
    // =====================================================================

    /**
     * Determine whether this homeowner may read the complaint: the one they
     * filed, and nothing else.
     */
    public function viewOwn(User $user, Complaint $complaint): bool
    {
        return $user->resident_id !== null && $complaint->resident_id === $user->resident_id;
    }

    /**
     * Determine whether this homeowner may file a complaint: their account is
     * tied to a profiled resident, which is who the complaint is filed against.
     */
    public function createOwn(User $user): bool
    {
        return $user->resident_id !== null;
    }
}
