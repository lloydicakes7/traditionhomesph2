<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\User;

/**
 * Bills — the Billing & Invoices page, the Monthly Dues pages, and everything
 * raised through them.
 *
 * The collections ledger keeps one bill per thing owed (see
 * \App\Models\Bill\Bill), so one policy guards every bill surface. Each ability
 * names the module it belongs to, because the two modules are granted
 * separately: an officer may be allowed to raise monthly dues and not to raise
 * ad-hoc invoices. The ability encodes which permission key it reads, and every
 * call site names the Bill class (`@can('viewAnyInvoices', Bill::class)`, the
 * form the rest of the panel uses).
 *
 * The portal shares this policy with one ability of its own — `payOwn` takes
 * the homeowner (`User`) rather than the officer, and answers only "is this
 * bill theirs?", which is the whole of the portal's authorization rule.
 */
class BillPolicy
{
    /** @var string The permission module for one-off invoices */
    const INVOICES = 'Invoices';

    /** @var string The permission module for the recurring monthly dues */
    const MONTHLY_DUES = 'Monthly Dues';

    // =====================================================================
    // One-off invoices — the Billing & Invoices page
    // =====================================================================

    /**
     * Determine whether the admin can browse the invoices page at all.
     */
    public function viewAnyInvoices(Admin $admin): bool
    {
        return $admin->hasPermission(self::INVOICES, 'View');
    }

    /**
     * Determine whether the admin can view one invoice.
     */
    public function viewInvoice(Admin $admin): bool
    {
        return $admin->hasPermission(self::INVOICES, 'View');
    }

    /**
     * Determine whether the admin can bill a homeowner.
     */
    public function createInvoice(Admin $admin): bool
    {
        return $admin->hasPermission(self::INVOICES, 'Create');
    }

    /**
     * Determine whether the admin can change an invoice.
     */
    public function updateInvoice(Admin $admin): bool
    {
        return $admin->hasPermission(self::INVOICES, 'Edit');
    }

    /**
     * Determine whether the admin can remove an invoice.
     */
    public function deleteInvoice(Admin $admin): bool
    {
        return $admin->hasPermission(self::INVOICES, 'Delete');
    }

    /**
     * Determine whether the admin can send invoices out of the system.
     */
    public function exportInvoices(Admin $admin): bool
    {
        return $admin->hasPermission(self::INVOICES, 'Export');
    }

    // =====================================================================
    // Monthly dues — the generator, the dues list and the table view
    // =====================================================================

    /**
     * Determine whether the admin can browse the dues pages at all.
     */
    public function viewAnyMonthlyDues(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'View');
    }

    /**
     * Determine whether the admin can view one month's dues.
     */
    public function viewMonthlyDue(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'View');
    }

    /**
     * Determine whether the admin can raise a month's invoices.
     */
    public function createMonthlyDue(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'Create');
    }

    /**
     * Determine whether the admin can change a month's dues.
     */
    public function updateMonthlyDue(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'Edit');
    }

    /**
     * Determine whether the admin can remove dues from the books.
     */
    public function deleteMonthlyDue(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'Delete');
    }

    /**
     * Determine whether the admin can send the dues list out of the system.
     */
    public function exportMonthlyDues(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'Export');
    }

    /**
     * Determine whether the admin can run the generator.
     */
    public function generateMonthlyDues(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'Create');
    }

    /**
     * Determine whether the admin can settle dues from a unit's credit.
     */
    public function applyAdvancePayment(Admin $admin): bool
    {
        return $admin->hasPermission(self::MONTHLY_DUES, 'Edit');
    }

    // =====================================================================
    // Homeowner portal — paying a bill
    // =====================================================================

    /**
     * Determine whether this homeowner may pay this bill: their own account's
     * bill and no other's. The portal is ownership-based, so the two ids are
     * compared as ints — the FK hydrates as a string on some drivers (the
     * reason AmenityBookingPolicy casts both sides too).
     */
    public function payOwn(User $user, Bill $bill): bool
    {
        return $bill->user_id !== null && (int) $bill->user_id === (int) $user->id;
    }
}
