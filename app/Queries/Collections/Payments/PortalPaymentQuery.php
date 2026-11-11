<?php

namespace App\Queries\Collections\Payments;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the homeowner's finance pages read: their own receipts and the bills
 * still open on their account.
 *
 * Both are read-only. No bill is paid from the portal — the homeowner settles
 * one at the HOA office — so this query has nothing to offer a payment form.
 */
class PortalPaymentQuery
{
    private BillOptionQuery $bills;

    public function __construct(BillOptionQuery $bills)
    {
        $this->bills = $bills;
    }

    // ===== Rows =====

    public function forUser(int $userId): Collection
    {
        return Payment::with(['bill', 'paymentMethod'])
            ->forUser($userId)
            ->ordered()
            ->get();
    }

    public function openBills(int $userId): Collection
    {
        return $this->bills->open($userId);
    }
}
