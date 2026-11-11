<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Payment;

/**
 * The payments ledger — the Payments page and the Record Payment action on
 * either bill list.
 *
 * Recording a receipt and correcting one are both permissions on the ledger
 * itself, because the ledger is the same whichever bill the money settled.
 *
 * The policy is the office's alone: money is received over the counter and
 * recorded by an officer, so the portal neither creates a payment nor prints a
 * receipt. A homeowner keeps no receipt ability here.
 */
class PaymentPolicy
{
    const MODULE = 'Payments';

    /**
     * Determine whether the admin can browse the payments ledger.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can view one payment.
     */
    public function view(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'View');
    }

    /**
     * Determine whether the admin can record money received.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can approve a payment the portal filed.
     *
     * Approving one is the act of recording money — the same permission the
     * office's own receipt takes (see \App\Enums\PaymentStatus); only this
     * money was handed over through the portal rather than the counter.
     */
    public function approve(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Create');
    }

    /**
     * Determine whether the admin can change a payment.
     */
    public function update(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Edit');
    }

    /**
     * Determine whether the admin can remove a payment from the ledger.
     */
    public function delete(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Delete');
    }

    /**
     * Determine whether the admin can send the ledger out of the system.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::MODULE, 'Export');
    }

}
