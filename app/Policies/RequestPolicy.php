<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Request;
use App\Models\User;

/**
 * Service requests, on both sides of the system.
 *
 * The admin side answers what an officer may do with the office's queue:
 * browse it, read one, file one on a homeowner's behalf, correct it, decide it
 * (approve or cancel), complete it and remove one from the record. Each maps to
 * the module's own permission, so who may touch the queue is the office's
 * decision. Printing is not an ability here — an approved request is printed on
 * the Print Documents page, which authorizes it there.
 *
 * The portal side answers the questions a homeowner can ask: "is this request
 * mine?", "may I file one?", "may I take a pending one back?" and "may I
 * download its slip?". A homeowner reads their own submissions and their
 * outcomes, files new ones, withdraws a filing the office has not acted on
 * yet, and downloads the transaction slip of an approved request. They never
 * edit one, never print the issued document and never reach another
 * household's request by any route to it.
 */
class RequestPolicy
{
    const MODULE = 'Requests';

    // =====================================================================
    // Admin side — the office's queue
    // =====================================================================

    /**
     * Determine whether the admin can browse the requests page at all.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one request.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can file a request on a homeowner's behalf.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can correct a request's contents.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can approve a request — accept it and unlock
     * the document it asked for.
     */
    public function approve(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can cancel a request.
     */
    public function cancel(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can complete an approved request once its
     * document has been released.
     */
    public function complete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a request from the record.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    // =====================================================================
    // Portal side — the homeowner's own submissions
    // =====================================================================

    /**
     * Determine whether this homeowner may read the request: the one they
     * filed, and nothing else.
     */
    public function viewOwn(User $user, Request $request): bool
    {
        return $this->owns($user, $request);
    }

    /**
     * Determine whether this homeowner may file a request: their account is
     * tied to a profiled resident, which is who the request is filed under.
     */
    public function createOwn(User $user): bool
    {
        return $user->resident_id !== null;
    }

    /**
     * Determine whether this homeowner may take back a request: their own, and
     * one the office has not acted on yet. A filing still pending is the only
     * one that can be withdrawn.
     */
    public function deleteOwn(User $user, Request $request): bool
    {
        return $this->owns($user, $request)
            && (bool) $request->status?->isPending();
    }

    /**
     * Determine whether this homeowner may download the transaction slip: the
     * slip is available once the office has approved the request.
     */
    public function downloadSlipOwn(User $user, Request $request): bool
    {
        return $this->owns($user, $request)
            && (bool) $request->status?->isApproved();
    }

    /**
     * Whether the request belongs to the signed-in homeowner's own resident.
     */
    private function owns(User $user, Request $request): bool
    {
        return $user->resident_id !== null && $request->resident_id === $user->resident_id;
    }
}
